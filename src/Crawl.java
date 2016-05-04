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

    static final int ID_NONE = 86;
    static final int ID_HUMAN = 87;
    static final int ID_JUVENILE = 6;
    static final int ID_ADULT = 5;
    static final int ID_MALE = 4;
    static final int ID_FEMALE = 3;
    static final int ID_AGE_UNKNOWN = 85;
    static final int ID_GENDER_UNKNOWN = 1;
    static final int ID_LIKE = 97;
    private Settings settings;
    Statement state;
    Statement updateClassifiedState;
    Statement getMedianState;

    public Crawl() {
        try {
            state = connectDataBase().createStatement(); //create a database connection
            updateClassifiedState = connectDataBase().createStatement();
            getMedianState = connectDataBase().createStatement();

            settings = new Settings(connectDataBase());
            settings.getNewSettings();
        } catch (SQLException ex) {
            System.out.println("SQL Error: " + ex.getMessage().toString());
        }
    }

    public Settings getSettings() {
        return settings;
    }

    public void getNewSettings() throws SQLException {
        settings.getNewSettings();
    }

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
            String host = "jdbc:mysql://calum-calder.com";
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
        DefaultHashMap<Integer,Integer>temp = new DefaultHashMap<Integer,Integer>();
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
            numerator += ((Double.parseDouble(value.toString()) / species.size()) * (Math.log(Double.parseDouble(value.toString()) / species.size())));
        }
        evenness = (-numerator) / Math.log(species.size());

        return evenness;
    }

    public Integer getMedianPlurality(Integer photo_id) throws SQLException {
        /**
         * Gets the median number of different species people have classified as being in a given photo.
         **/
        String query = "SELECT COUNT(*) AS count FROM " +
                "(SELECT `species`, `person_id` FROM (SELECT * FROM `MammalWeb`.`Animal` where photo_id=" +
                photo_id.toString() + " AND species != " + ID_LIKE + " LIMIT 0," + settings.getMaxVotes() + ") as LimitedVotes " +
                "GROUP BY `person_id`, `species`) AS GroupedVotes " +
                "GROUP BY `person_id` ORDER BY count;";

        ResultSet countsResultSet = getMedianState.executeQuery(query);
        ArrayList<Integer> counts = new ArrayList<Integer>();

        while (countsResultSet.next())
            counts.add(countsResultSet.getInt("count"));

        return counts.get((int) Math.ceil((counts.size() - 1)/2.0));
    }

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

        for(Integer speciesID : voteSpecies) {
            speciesCount.put(speciesID, speciesCount.getOrDefault(speciesID, 0) + 1);
        }

        return getConsensusFromSpeciesCount(speciesCount, count);
    }

    public ArrayList<Integer> getConsensusFromSpeciesCount(HashMap<Integer, Integer> countMap, Integer count) {
        /**
         * Calculates the top <count> classifications from a count of each species vote.
         **/
        ArrayList<Integer> ordered = new ArrayList<Integer>(countMap.keySet());
        Collections.sort(ordered, new CountComparator(countMap));

        ArrayList<Integer> res = new ArrayList<Integer>();
        for (int i = 0; i < count; i++) {
            res.add(ordered.get(i));
        }

        return res;
    }

    public ArrayList<Integer> calculateAges(ClassificationMatrix classMatrix, ArrayList<Integer> species_classifications) {
        /**
         * Calculates the ages of given classifications from a classification matrix.
         **/

        ArrayList<Integer> ages = new ArrayList<Integer>();
        for (Integer species : species_classifications) {
            ages.add(calculateAge(classMatrix, species));
        }

        return ages;
    }

    public Integer calculateAge(ClassificationMatrix classMatrix, Integer species) {
        ArrayList<Integer> species_ids = classMatrix.getSpecies();
        ArrayList<Integer> age_ids = classMatrix.getAges();
        ArrayList<Integer> ages = new ArrayList<Integer>();
        for (int i = 0; i < species_ids.size(); i++) {
            if (species_ids.get(i).equals(species)) {
                ages.add(age_ids.get(i));
            }
        }

        // Count each age vote
        DefaultHashMap<Integer,Integer> agesCounter = new DefaultHashMap<Integer,Integer>();
        for(Integer age: ages) {
            agesCounter.put(age, agesCounter.getOrDefault(age, 0) + 1);
        }

        int voteCount = ages.size();
        Integer minCount = (int) (settings.getMinAgeProportion()*voteCount);
        Integer juvenileCount = agesCounter.getOrDefault(ID_JUVENILE, 0);
        Integer adultCount = agesCounter.getOrDefault(ID_ADULT, 0);

        if (juvenileCount > minCount && juvenileCount > adultCount) {
            return ID_JUVENILE;
        } else if (adultCount > minCount) {
            return ID_ADULT;
        } else {
            return ID_AGE_UNKNOWN;
        }

    }

    public ArrayList<Integer> calculateGenders(ClassificationMatrix classMatrix, ArrayList<Integer> species_classifications) {
        /**
         * Calculates the genders of given classifications from a classification matrix.
         **/

        ArrayList<Integer> genders = new ArrayList<Integer>();
        for (Integer species : species_classifications) {
            genders.add(calculateGender(classMatrix, species));
        }

        return genders;
    }

    public Integer calculateGender(ClassificationMatrix classMatrix, Integer species) {
        ArrayList<Integer> species_ids = classMatrix.getSpecies();
        ArrayList<Integer> gender_ids = classMatrix.getGenders();
        ArrayList<Integer> genders = new ArrayList<Integer>();
        for (int i = 0; i < species_ids.size(); i++) {
            if (species_ids.get(i).equals(species)) {
                genders.add(gender_ids.get(i));
            }
        }

        // Count each gender vote
        DefaultHashMap<Integer,Integer> genderCounter = new DefaultHashMap<Integer,Integer>();
        for(Integer gender: genders) {
            genderCounter.put(gender, genderCounter.getOrDefault(gender, 0) + 1);
        }

        int voteCount = genders.size();
        Integer minCount = (int) (settings.getMinGenderProportion()*voteCount);
        Integer femaleCount = genderCounter.getOrDefault(ID_FEMALE, 0);
        Integer maleCount = genderCounter.getOrDefault(ID_MALE, 0);

        if (femaleCount> minCount && femaleCount > maleCount) {
            return ID_FEMALE;
        } else if (maleCount > minCount) {
            return ID_MALE;
        } else {
            return ID_GENDER_UNKNOWN;
        }

    }

    public void updatePhoto(Integer photo_id) throws SQLException {
        /**
         * Updates a given photo.
         * Calculates consecutive blanks, consensus or evenness for the photo and updates database accordingly.
         **/

        ClassificationMatrix classMatrix = getPhotoVotes(photo_id);
        ArrayList<Integer> species = classMatrix.getSpecies();
        ArrayList<Integer> ages = classMatrix.getAges();
        ArrayList<Integer> genders = classMatrix.getGenders();

        Integer speciesCount = getMedianPlurality(photo_id);
        ArrayList<Integer> option_ids;

        if (consecutiveBlanks(species)) {
            option_ids = new ArrayList<Integer>() {{ this.add(ID_NONE); }};
            classifyImage(option_ids, option_ids, option_ids,photo_id);
            return;
        }

        option_ids = consensus(species, speciesCount);
        if (!option_ids.contains(new Integer(-1))) {
            classifyImage(option_ids, calculateAges(classMatrix, option_ids),calculateGenders(classMatrix,option_ids), photo_id);
            return;
        }

        option_ids = consensusNoMin(species, speciesCount);
        if (species.size() >= 25) {
            double evenness = calculateEvenness(species);
            setEvenness(evenness, photo_id);
            if (evenness > settings.getEvennessThreshold()) {
                classifyImage(option_ids, calculateAges(classMatrix, option_ids),calculateGenders(classMatrix,option_ids), photo_id);
            }
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

    public void classifyImage(ArrayList<Integer> option_ids, ArrayList<Integer> age_ids,ArrayList<Integer> gender_ids, Integer photo_id) {
        /**
         * Classifies a given image with a set of classifications
         **/
        try {
            for (int i = 0; i < option_ids.size(); i++) {
                String updateQuery =
                        "INSERT INTO `MammalWeb`.`XClassification`(`photo_id`, `species`, `age`, `gender`)" +
                                "VALUES (" + photo_id.toString() + "," + option_ids.get(i) + ',' + age_ids.get(i) + ',' + gender_ids.get(i) + ")";
                updateClassifiedState.executeUpdate(updateQuery);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public ClassificationMatrix getPhotoVotes(Integer photo_id) throws SQLException {
        /**
         * Gets a list of votes on a given photo
         **/
        ArrayList<Integer> species = new ArrayList<Integer>();
        ArrayList<Integer> ages = new ArrayList<Integer>();
        ArrayList<Integer> genders = new ArrayList<Integer>();
        String query = "SELECT species, gender, age FROM MammalWeb.Animal WHERE photo_id = " + photo_id.toString() + " AND species != " + ID_LIKE + " LIMIT 0," + settings.getMaxVotes() + ";";
        ResultSet relatedPhotos = state.executeQuery(query);

        while(relatedPhotos.next()) {
            species.add(relatedPhotos.getInt("species"));
            ages.add(relatedPhotos.getInt("age"));
            genders.add(relatedPhotos.getInt("gender"));
        }

        return new ClassificationMatrix(species, ages, genders);
    }

    public boolean classifyImages() {
        // Get unclassified photos in ascending order
        String getAnimalInstance = "SELECT animal_id, photo_id FROM MammalWeb.Animal WHERE `photo_id` NOT IN (SELECT `photo_id` FROM MammalWeb.XClassification) AND species != " + ID_LIKE + " AND `animal_id` > " + settings.getLastClassifiedID() + " ORDER BY animal_id";
        String getPhotos = null;
        try {
            ResultSet animalInstance = null;
            ResultSet countPhotoInstances = null;
            ResultSet getPhotoInstances = null;

            // Generate set of photos to be updated
            animalInstance = state.executeQuery(getAnimalInstance);
            LinkedHashSet<Integer> photosToUpdate = new LinkedHashSet<Integer>();
            int id = 0;
            int maxID = 0;
            while (animalInstance.next()) {
                photosToUpdate.add(animalInstance.getInt("photo_id"));
                id = animalInstance.getInt("animal_id");
                if (id > maxID) {
                    maxID = id;
                }
            }
            // Update all photos in set
            updatePhotos(photosToUpdate);
            settings.setLastClassifiedID(maxID);

        }catch(Exception e){
            e.printStackTrace();
            return false;
        }


        return true;
    }

    public static void main(String[] args)
    {
        Crawl crawl = new Crawl();
        while (true) {
                try {
                    crawl.getNewSettings();
                } catch (SQLException e) {
                    System.err.println("Error getting crawler settings.");
                    e.printStackTrace();
                    break;
                }

                if (crawl.getSettings().getRunFlag())
                    crawl.classifyImages();

                try {
                    Thread.sleep(crawl.getSettings().getRunFrequency()*1000);
                } catch (Exception e) {
                    e.printStackTrace();
                }
        }
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

class ClassificationMatrix {
    private ArrayList<Integer> species_ids;
    private ArrayList<Integer> age_ids;
    private ArrayList<Integer> gender_ids;

    public ClassificationMatrix(ArrayList<Integer> species_ids, ArrayList<Integer> age_ids, ArrayList<Integer> gender_ids) {
        this.species_ids = species_ids;
        this.age_ids = age_ids;
        this.gender_ids = gender_ids;
    }

    public ArrayList<Integer> getSpecies() {
        return this.species_ids;
    }

    public ArrayList<Integer> getAges() {
        return this.age_ids;
    }

    public ArrayList<Integer> getGenders() {
        return this.gender_ids;
    }
}

class Settings{

    private Integer minConsensusCount = 0;
    private Integer runFrequency = 0;
    private Integer maxVotes = 0;
    private Integer lastClassifiedID = 0;
    private boolean runFlag = false;
    private Double minAgeProportion = 0.4;
    private Double minGenderProportion = 0.4;
    private Double evennessThreshold = 0.4;
    private Connection con;
    private Statement statement;

    private static final int SETTING_CONSENSUSCOUNT = 1;
    private static final int SETTING_RUNFLAG = 2;
    private static final int SETTING_RUNFREQUENCY = 3;
    private static final int SETTING_MINAGEPROPORTION = 4;
    private static final int SETTING_EVENNESS_THRESHOLD = 5;
    private static final int SETTING_MAX_VOTES = 6;
    private static final int SETTING_MINGENDERPROPORTION = 7;
    private static final int SETTING_LASTCLASSIFIEDID = 8;

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
    public double getMinAgeProportion() {
        return minAgeProportion/100.0;
    }
    public double getEvennessThreshold() {
        return evennessThreshold/100.0;
    }
    public int getMaxVotes() {
        return maxVotes;
    }
    public double getMinGenderProportion() {
        return minGenderProportion/100.0;
    }
    public int getLastClassifiedID() {
        return lastClassifiedID;
    }

    public void setLastClassifiedID(int id) throws SQLException {
        String updateQuery = "UPDATE  `MammalWeb`.`CrawlerSettings` SET  `setting_value` = '" + id + "' WHERE  `CrawlerSettings`.`setting_id` = " + SETTING_LASTCLASSIFIEDID + ";";
        statement.executeUpdate(updateQuery);
        lastClassifiedID = id;
    }

    public void getNewSettings() throws SQLException {
        String querySettings = "SELECT setting_id, setting_value FROM MammalWeb.CrawlerSettings";

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
                    runFlag = settings.getInt("setting_value") != 0;
                    break;
                case SETTING_MINAGEPROPORTION:
                    minAgeProportion = settings.getInt("setting_value")/100.0;
                    break;
                case SETTING_MINGENDERPROPORTION:
                    minGenderProportion = settings.getInt("setting_value")/100.0;
                    break;
                case SETTING_EVENNESS_THRESHOLD:
                    evennessThreshold = settings.getInt("setting_value")/100.0;
                    break;
                case SETTING_MAX_VOTES:
                    maxVotes = settings.getInt("setting_value");
                    break;
                case SETTING_LASTCLASSIFIEDID:
                    lastClassifiedID = settings.getInt("setting_value");
                    break;
            }
        }
    }
}
