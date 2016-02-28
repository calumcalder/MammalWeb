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
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedHashMap;

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

    public Integer  getRowInTable(ResultSet animalInstance,Statement state) //convert animal_id 'index' to row number so result set can be changed to the correct
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


    Statement state;
    Statement InsertState;
    Integer currentIndexLimit;
    Integer lastID = null;
    Integer setRow = 0;

    public boolean classifyImages() {
        try {
            state = connectDataBase().createStatement(); //create a database connection
            InsertState = connectDataBase().createStatement();
        } catch (SQLException ex) {
            System.out.println("SQL Error: " + ex.getMessage().toString());
            return false;
        }

        String getAnimalInstance = "SELECT animal_id, species, person_id, gender, age, number, photo_id FROM MammalWeb.Animal ORDER BY animal_id"; //get photo in ascending order
        String getPhotos = null;
        try{
            ResultSet animalInstance = null;
            ResultSet countPhotoInstances = null;
            ResultSet getPhotoInstances = null;

        Integer row = getRowInTable(animalInstance,InsertState);
        animalInstance = state.executeQuery(getAnimalInstance);

        animalInstance.absolute(row);
        while (animalInstance.next()) {
            ArrayList<Integer>species = new ArrayList<Integer>();
            ArrayList<Integer>animalId = new ArrayList<Integer>();
            ArrayList<Integer>personId = new ArrayList<Integer>();
            ArrayList<Integer>gender = new ArrayList<Integer>();
            ArrayList<Integer>age = new ArrayList<Integer>();
            ArrayList<Integer>number = new ArrayList<Integer>();

            System.out.println("Animal ID: " + animalInstance.getString("animal_id"));
            getPhotos = "SELECT animal_id,photo_id, species, person_id, gender, age, number, photo_id FROM MammalWeb.Animal WHERE photo_id="+animalInstance.getString("photo_id");//"SELECT COUNT("+animalInstance.getString("photo_id")+")AS COUNTIMAGES FROM MammalWeb.Animal WHERE photo_id="+animalInstance.getString("photo_id");
            getPhotoInstances = InsertState.executeQuery(getPhotos);
            while(getPhotoInstances.next()) {
                species.add(Integer.parseInt(getPhotoInstances.getString("species")));
                animalId.add(Integer.parseInt(getPhotoInstances.getString("animal_id")));
                personId.add(Integer.parseInt(getPhotoInstances.getString("person_id")));
                gender.add(Integer.parseInt(getPhotoInstances.getString("gender")));
                age.add(Integer.parseInt(getPhotoInstances.getString("age")));
                number.add(Integer.parseInt(getPhotoInstances.getString("number")));
            }
            Double result = calculateEvenness(species);
            System.out.println(result);
            lastID = Integer.parseInt(animalInstance.getString("animal_id"));
        }
        writeIndex("index.txt",lastID);
        }catch(Exception e){
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

