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
 * Created by Calum and Stefan.
 */
public class Crawl {
	
    public Crawl() {
	try {
		state = connectDataBase().createStatement(); //create a database connection
		updateClassifiedState = connectDataBase().createStatement();
		updateXClassifiedState = connectDataBase().createStatement();
		getMedianState = connectDataBase().createStatement();
		InsertState = connectDataBase().createStatement();
	} catch (SQLException ex) {
		System.out.println("SQL Error: " + ex.getMessage().toString());
	} 
    }

    static final int ID_NONE = 86;
    static final int ID_HUMAN = 87;
    static final int ID_CONSENSUS = 10;
    static final int ID_SPECIES= 10;
    static final int ID_CONSECUTIVE = 5;
    static final double EVENNESS_THRESHOLD = 0.3;

    public Connection connectDataBase() {
	/**
	 * Initialises the connection to the database.
	 **/
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
	/**
	 * Calculates the evenness of a given set of votes.
	 **/
        DefaultHashMap<Integer,Integer>temp = new DefaultHashMap<Integer,Integer>();//get frequencies
        for(Integer speciesID : species) {
            temp.put(speciesID, temp.getOrDefault(speciesID, 0) + 1);
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

    public Integer getMedianPlurality(Integer photo_id) throws SQLException {
	/**
	 * Gets the median number of different species people have classified as being in a given photo.
	 **/
    	String query = "SELECT COUNT(*) AS count FROM " + 
	"(SELECT `species`, `person_id` FROM `MammalWeb`.`Animal` where photo_id=" +
	photo_id.toString() + " " +
	"GROUP BY `person_id`, `species`) AS GroupedVotes " +
	"GROUP BY `person_id` ORDER BY count;";
	
	ResultSet countsResultSet = getMedianState.executeQuery(query);
	ArrayList<Integer> counts = new ArrayList<Integer>();
	
	while (countsResultSet.next())
		counts.add(countsResultSet.getInt("count"));
	
	return counts.get((int) Math.ceil((counts.size() - 1)/2.0));
    }

    Statement state;
    Statement updateClassifiedState;
    Statement updateXClassifiedState;
    Statement getMedianState;
    Statement InsertState;
    Integer lastID = null;
    Integer setRow = 0;

    public void updatePhotos(Set<Integer> photosToUpdate) throws SQLException {
	 /**
	  * Updates a set of photos.
	  */
         for (Integer photo_id : photosToUpdate) {
             updatePhoto(photo_id);
         }
    }

    public boolean consecutiveBlanks(ArrayList<Integer> voteSpecies) {
	/**
	 * Calculates if there are 5 consecutive blank votes.
	 **/
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

    public ArrayList<Integer> consensus(ArrayList<Integer> voteSpecies, Integer count){
	/**
	 * Calculates the consensus of votes, taking in to account the minimum 10 votes requirement.
	 **/
        DefaultHashMap<Integer,Integer> speciesCount = new DefaultHashMap<Integer,Integer>();
        Integer cur = 0;
        for(Integer speciesID : voteSpecies) {
                speciesCount.put(speciesID, speciesCount.getOrDefault(speciesID, 0) + 1);
	}
	ArrayList<Integer> res = getConsensusFromSpeciesCount(speciesCount, count);
	for (Integer species : res) {
		if (speciesCount.get(species) < 10) 
			return new ArrayList<Integer>() {{ this.add(-1); }};
	}
	
	return res;
    }
    
    public ArrayList<Integer> consensusNoMin(ArrayList<Integer> voteSpecies, Integer count) {
	/**
	 * Calculates the consensus of votes, ignoring the minimum 10 votes requirement.
	 **/
	DefaultHashMap<Integer,Integer>speciesCount = new DefaultHashMap<Integer,Integer>();
	Integer cur;
        for(Integer speciesID : voteSpecies) {
                speciesCount.put(speciesID, speciesCount.getOrDefault(speciesID, 0) + 1);
	}
	
	return getConsensusFromSpeciesCount(speciesCount, count);
    }
    
    public ArrayList<Integer> getConsensusFromSpeciesCount(HashMap<Integer, Integer> speciesCount, Integer count) {
	/**
	 * Calculates the top <count> classifications from a count of each species vote.
	 **/
	ArrayList<Integer> ordered = new ArrayList<Integer>(speciesCount.keySet());
	
	Collections.sort(ordered, new CountComparator(speciesCount));
	ArrayList<Integer> res = new ArrayList<Integer>();
	for (int i = 0; i < count; i++) {
		res.add(ordered.get(i));
	}
	
	return res;
    }

    public void updatePhoto(Integer photo_id) throws SQLException {
	/**
	 * Updates a given photo.
	 * Calculates consecutive blanks, consensus or evenness for the photo and updates database accordingly.
	 **/
        ArrayList<Integer> species = getPhotoVotes(photo_id);
	Integer speciesCount = getMedianPlurality(photo_id);
	ArrayList<Integer> option_ids;
	
        if (consecutiveBlanks(species)) {
	    option_ids = new ArrayList<Integer>() {{ this.add(ID_NONE); }};
            classifyImage(option_ids, photo_id);
	    return;
        }
	
	option_ids = consensus(species, speciesCount);
        if (!option_ids.contains(new Integer(-1))) {
            classifyImage(option_ids, photo_id);
	    return;
        }
	
	option_ids = consensusNoMin(species, speciesCount);
        if (species.size() >= 25) {
	    double evenness = calculateEvenness(species);
	    setEvenness(evenness, photo_id);
	    return;
        }
	System.out.println("photo_id: " + photo_id + "\tdoes not have enough votes to be classified");
    }
    
    public void setEvenness(Double evenness, Integer photo_id) throws SQLException {
	    /**
	     * Sets the evenness of a given photo
	     **/
	    String updateQuery = "UPDATE `MammalWeb`.`Photo` SET `evenness` = " + evenness.toString() + "WHERE `photo_id` = " + photo_id.toString() + ";";
	    updateClassifiedState.executeUpdate(updateQuery);
    }
    
    public void classifyImage(ArrayList<Integer> option_ids, Integer photo_id) {
	/**
	 * Classifies a given image with a set of classifications
	 **/
	try {
		for (Integer option_id : option_ids) {
			String blanks = option_id == ID_NONE ? "1" : "0"; 
			String updateQuery = "INSERT INTO `MammalWeb`.`XClassification`(`person_id`, `photo_id`, `humans`, `nothing`, `species`)" +
					     "VALUES (-1," + photo_id.toString() + ",0," + blanks + "," + option_id.toString() + ")";
			updateClassifiedState.executeUpdate(updateQuery);
		}
	} catch (Exception e) {
		e.printStackTrace();
	}
    }

    public ArrayList<Integer> getPhotoVotes(Integer photo_id) throws SQLException {
	/**
	 * Gets a list of votes on a given photo
	 **/
        ArrayList<Integer> species = new ArrayList<Integer>();
        String query = "SELECT species FROM MammalWeb.Animal WHERE photo_id = " + photo_id.toString() + ";";
        ResultSet relatedPhotos = state.executeQuery(query);

        while(relatedPhotos.next())
            species.add(relatedPhotos.getInt("species"));

        return species;
    }

    public boolean classifyImages() {
	// Get unclassified photos in ascending order
        String getAnimalInstance = "SELECT animal_id, photo_id FROM MammalWeb.Animal WHERE `photo_id` NOT IN (SELECT `photo_id` FROM MammalWeb.XClassification) ORDER BY animal_id"; 
        String getPhotos = null;
        try {
            ResultSet animalInstance = null;
            ResultSet countPhotoInstances = null;
            ResultSet getPhotoInstances = null;

	    // Generate set of photos to be updated
            animalInstance = state.executeQuery(getAnimalInstance);
            LinkedHashSet<Integer> photosToUpdate = new LinkedHashSet<Integer>();
            while (animalInstance.next()) {
                photosToUpdate.add(animalInstance.getInt("photo_id"));
            }
	    // Update all photos in set
            updatePhotos(photosToUpdate);
	    
        }catch(Exception e){
	    e.printStackTrace();
            return false;
        }


        return true;
    }

    public static void main(String[] args)
    {
        Crawl crawl = new Crawl();
        try {
//        	System.out.println(crawl.getMedianPlurality(10000));
	        //crawl.updatePhoto(160);
		Settings s = new Settings(crawl.connectDataBase());
		s.getNewSettings();
		System.out.println(s.getMinConsensusCount());
        } catch (Exception e) {
        	e.printStackTrace();
        }
        //crawl.classifyImages();
	
	
	
    }

}

class CountComparator implements Comparator<Integer> {
	private HashMap<Integer, Integer> speciesCount;
	
	public CountComparator(HashMap<Integer,Integer> speciesCount) {
		this.speciesCount = speciesCount;
	}
	
	public int compare(Integer i1, Integer i2) {
		return speciesCount.get(i2) - speciesCount.get(i1);
	}
	
}

class DefaultHashMap<K, V> extends HashMap<K, V> {
	
	public V getOrDefault(K key, V def) {
		V get = this.get(key);
		
		if (get == null)
			return def;
		else
			return get;
	}
	
}

class Settings{
	
	private Integer minConsensusCount = 0;
	private Integer runFrequency = 0;
	private boolean runFlag = false;
	private Connection con;
	private Statement statement;
	
	private static final int SETTING_CONSENSUSCOUNT = 1;
	private static final int SETTING_RUNFREQUENCY = 2;
	private static final int SETTING_RUNFLAG = 3;
	
	public Settings(Connection con) throws SQLException {
		this.con = con;
		this.statement = con.createStatement();
	};
	
	public Integer getMinConsensusCount(){ //get minimum consensus for count
		return minConsensusCount;
	}
	public Integer getRunFrequency(){ //get interval check frequency
		return runFrequency;
	}
	public boolean getRunFlag(){ //get running flag for dashboard run and stop...
		return runFlag;
	}
	
	public void getNewSettings() throws SQLException {
		String querySettings = "SELECT setting_id, setting_name, setting_value FROM MammalWeb.CrawlerSettings"; //fixed.
		
		ResultSet settings = statement.executeQuery(querySettings); 
		while(settings.next()){
			switch (settings.getInt("setting_id")) {
				case SETTING_CONSENSUSCOUNT:
					minConsensusCount = settings.getInt("setting_value");
					break;
				case SETTING_RUNFREQUENCY:
					runFrequency = settings.getInt("setting_value");
					break;
				case SETTING_RUNFLAG:
					runFlag = settings.getBoolean("setting_value");
					break;
			}
		}
	}
}

