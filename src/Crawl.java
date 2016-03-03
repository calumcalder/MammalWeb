import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.OutputStream;
import java.nio.charset.Charset;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.*;
import static java.nio.file.StandardOpenOption.*;
import java.nio.file.*;
import java.io.*;
import java.util.*;

/**
 * Created by Stefan on 25/02/2016.
 */
public class Crawl {
    public Crawl()
    {}

    static final int ID_NONE = 86;
    static final int ID_HUMAN = 87;
    static final int ID_CONSENSUS = 10;
    static final int ID_SPECIES= 10;
    static final int ID_CONSECUTIVE = 5;
    static final double EVENNESS_THRESHOLD = 0.3;

    public Connection connectDataBase() {
        Connection con = null;
        try {
            Class.forName("com.mysql.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            System.out.println(e.getMessage());
        }
        try {
            String host = "jdbc:mysql://calum-calder.com:3306";
            String uName = "admin3";
            String uPass = "admin";
            con = DriverManager.getConnection(host, uName, uPass);
        } catch (SQLException err) {
            System.out.println(err.getMessage());
        }
        return con;
    }


    private double calculateEvenness(ArrayList<Integer> species) {

        LinkedHashMap<Integer,Integer>temp = new LinkedHashMap<Integer,Integer>();//get frequencies
        for(int j = 0; j <  species.size(); j++) {
            if(temp.get(species.get(j)) == null){
                temp.put(species.get(j),1);
            }
            else if(temp.get(species.get(j)) != null){
                temp.put(species.get(j),temp.get(species.get(j))+1);
            }
        }

        if (temp.size() == 1) //all agree avoid processing.
        {
            return 0.0;
        }
        double numerator = 0;
        double evenness = 0;

        for (Object value : temp.values()) {
            numerator += (double) ((Double.parseDouble(value.toString()) / species.size()) * (Math.log(Double.parseDouble(value.toString()) / species.size())));
        }
        evenness = (-numerator) / Math.log(species.size());

        return evenness;

    }

    public Integer readIndex(String filename)
    {
        Path file = Paths.get(filename);
        Charset charset = Charset.forName("US-ASCII");
        try (BufferedReader reader = Files.newBufferedReader(file, charset)) {
            String line = null;
            while ((line = reader.readLine()) != null) {
                return Integer.parseInt(line);
            }
        } catch (IOException x) {
            System.err.format("IOException: %s%n", x);
        }
        return 0;
    }

    public Integer writeIndex(String filename,Integer Index) //index- where the algorithm has currently processed.
    {
        // Convert the string to a
        // byte array.
        String s = Index.toString();
        byte data[] = s.getBytes();
        Path p = Paths.get(filename);

        try (OutputStream out = new BufferedOutputStream(
                Files.newOutputStream(p, CREATE, WRITE))) {
            out.write(data, 0, data.length);
        } catch (IOException x) {
            System.err.println(x);
        }
        return 0;
    }

    public Integer getRowInTable(ResultSet animalInstance,Statement state) //convert animal_id 'index' to row number so result set can be changed to the correct
    {
        Integer index = readIndex("index.txt");
        Integer row = 0;
        String query = "SELECT COUNT(*) AS row FROM MammalWeb.Animal WHERE animal_id  <= (SELECT animal_id FROM  MammalWeb.Animal WHERE animal_id = '"+index+"')";

                try {
                    animalInstance = state.executeQuery(query);
                    if(animalInstance.next()){
                        row = Integer.parseInt(animalInstance.getString("row"));
                        System.out.println("Current Row at: " + animalInstance.getString("row"));
                    }
                }
                catch(SQLException e) {
                    return 0;
                }
        return row;
    }

    public boolean setClassifiedFlag(Integer animal_id, Statement state) {
        try {
            String queryString = "UPDATE  `MammalWeb`.`Animal` SET  `classified` =  '1' WHERE  `Animal`.`animal_id` = " + animal_id.toString() + ";";
            state.executeUpdate(queryString);
            return true;
        } catch (SQLException e) {
            e.printStackTrace();
            return false;
        }
    }

    Statement state;
    Statement updateClassifiedState;
    Statement updateXClassifiedState;
    Statement InsertState;
    Integer lastID = null;
    Integer setRow = 0;

    public void updatePhotos(Set<Integer> photosToUpdate) throws SQLException {
         for (Integer photo_id : photosToUpdate) {
             updatePhoto(photo_id);
         }
    }

    public boolean consecutiveBlanks(ArrayList<Integer> voteSpecies) {
        if (voteSpecies.size() < 5)
            return false;

        int consecutive = 0;
        for (Integer species : voteSpecies) {
            if (species == ID_NONE)
                consecutive += 1;
            else
                consecutive = 0;

            if (consecutive >= 5)
                return true;
        }

        return false;
    }

    public Integer consensus(ArrayList<Integer> voteSpecies){
        HashMap<Integer,Integer>speciesCount = new HashMap<Integer,Integer>();
        for(Integer speciesID : voteSpecies)
            speciesCount.put(
                    speciesID,
                    speciesCount.getOrDefault(speciesID, 0) + 1
            );
        Integer consensusID = -1;
        Integer countMax = 0;
        for(Integer key : speciesCount.keySet()) {
            Integer votes = speciesCount.get(key);
            if (votes > countMax && votes >= 10) {
                consensusID = key;
                countMax = votes;
            }
        }
        return consensusID;
    }
    
    public Integer consensusNoMin(ArrayList<Integer> voteSpecies) {
	HashMap<Integer,Integer>speciesCount = new HashMap<Integer,Integer>();
        for(Integer speciesID : voteSpecies)
            speciesCount.put(
                    speciesID,
                    speciesCount.getOrDefault(speciesID, 0) + 1
            );
        Integer consensusID = -1;
        Integer countMax = 0;
        for(Integer key : speciesCount.keySet()) {
            Integer votes = speciesCount.get(key);
            if (votes > countMax) {
                consensusID = key;
                countMax = votes;
            }
        }
        return consensusID;   
    }

    public void updatePhoto(Integer photo_id) throws SQLException {
        ArrayList<Integer> species = getPhotoVotes(photo_id);
	
        if (consecutiveBlanks(species)) {
            classifyImage(ID_NONE, photo_id);
	    System.out.println("photo_id: " + photo_id + "\tClassified as blank with 5 consecutive votes.");
	    return;
        }
	Integer option_id = consensus(species);
        if (!option_id.equals(new Integer(-1))) {
            classifyImage(option_id, photo_id);
	    System.out.println("photo_id: " + photo_id + "\tClassified with 10 votes as: " + option_id);
	    return;
        }
        if (species.size() >= 25) {
	    double evenness = calculateEvenness(species);
            if (evenness < EVENNESS_THRESHOLD) {
	        option_id = consensusNoMin(species);
		classifyImage(option_id, photo_id);
		System.out.println("photo_id: " + photo_id + "\tClassified with evenness: " + evenness);
		for (Integer id : species) { System.out.print("" + id + ", "); }
		System.out.println("");
		return;
	    } else {
		System.out.println("photo_id: " + photo_id + " flagged");
		return;
	    }
        }
	System.out.println("photo_id: " + photo_id + "\tdoes not have enough votes to be classified");
    }
    
    public void classifyImage(Integer option_id, Integer photo_id) {
	try {
		String updateQuery = "UPDATE MammalWeb.Photo" + 
				    " SET classification_id=" + option_id.toString() +
				    " WHERE photo_id=" + photo_id.toString() + ";";
		updateClassifiedState.executeUpdate(updateQuery);
	} catch (Exception e) {
		e.printStackTrace();
	}
    }

    public ArrayList<Integer> getPhotoVotes(Integer photo_id) throws SQLException {
        ArrayList<Integer> species = new ArrayList<Integer>();
        String query = "SELECT species FROM MammalWeb.Animal WHERE photo_id = " + photo_id.toString() + ";";
        ResultSet relatedPhotos = state.executeQuery(query);

        while(relatedPhotos.next())
            species.add(relatedPhotos.getInt("species"));

        return species;
    }

    public boolean classifyImages() {
        try {
            state = connectDataBase().createStatement(); //create a database connection
	    updateClassifiedState = connectDataBase().createStatement();
	    updateXClassifiedState = connectDataBase().createStatement();
            InsertState = connectDataBase().createStatement();
        } catch (SQLException ex) {
            System.out.println("SQL Error: " + ex.getMessage().toString());
            return false;
        }

        String getAnimalInstance = "SELECT animal_id, photo_id FROM MammalWeb.Animal WHERE classified = 0 ORDER BY animal_id"; //get photo in ascending order
        String getPhotos = null;
        try {
            ResultSet animalInstance = null;
            ResultSet countPhotoInstances = null;
            ResultSet getPhotoInstances = null;

            //Integer row = getRowInTable(animalInstance, InsertState);
            animalInstance = state.executeQuery(getAnimalInstance);
            LinkedHashSet<Integer> photosToUpdate = new LinkedHashSet<Integer>();
            //animalInstance.absolute(row);
            while (animalInstance.next()) {
                photosToUpdate.add(animalInstance.getInt("photo_id"));
                lastID = Integer.parseInt(animalInstance.getString("animal_id"));
            }
	    System.out.println("Calculated photos to update");
            updatePhotos(photosToUpdate);
            writeIndex("index.txt", lastID);
        }catch(Exception e){
	    e.printStackTrace();
            return false;
        }


        return true;
    }

    public static void main(String[] args)
    {
        Crawl crawl = new Crawl();
        crawl.classifyImages();

        return;
    }

}

