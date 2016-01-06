import java.sql.*;
import java.util.*;
import java.lang.Math;

public class SQLLoader {

    public  SQLLoader()
    {

    }
    int currentIndex = 0;
    int currentIndexLimit = 0;
    public Connection connectDataBase()
    {
        Connection con = null;
        try {
            Class.forName("com.mysql.jdbc.Driver");
        }
        catch(ClassNotFoundException e) {
            System.out.println(e.getMessage());
        }
        try {
            String host = "jdbc:mysql://127.0.0.1:3306/test";
            String uName = "";
            String uPass = "";
            con = DriverManager.getConnection(host, uName, uPass);
        }
        catch(SQLException err) {
            System.out.println(err.getMessage());
        }
        return con;
    }
    private double calculateEvenness(HashMap temp, double total)
    {
        if(temp.size() == 1) //all agree avoid processing.
        {
            return 0;
        }
        double numerator = 0;
        double evenness = 0;

        for(Object value : temp.values())
        {
            numerator += (double)((Double.parseDouble(value.toString())/total)*(Math.log(Double.parseDouble(value.toString())/total)));
        }
        evenness = (-numerator)/Math.log(temp.size());

        return evenness;
    }


    private int getTableSize()
    {
        int size = 0;
        try {
            Statement count = null;
            count = connectDataBase().createStatement();
            String query = "SELECT COUNT(*) FROM animal";
            ResultSet countRs = count.executeQuery(query); //getTableSize needs to be wrapped in a timer every 5 minutes use for checking whether to update or not.
            while(countRs.next())
            {
                size = countRs.getInt(1);
            }
        }
        catch(SQLException err) {
            return 0;
        }
        return size;
    }

    public boolean classifyImages()
    {

        Statement state = null;
        String query = "";
        ResultSet rs = null;
        try {
            state = connectDataBase().createStatement(); //one connection needed
            currentIndexLimit = getTableSize();
        }
        catch (SQLException ex) {
            return false;
        }

        Set<Integer> photoClassified = new TreeSet<Integer>();
        while(currentIndex != currentIndexLimit) {
            double total = 0;
            boolean fractionalBlank = false;
            HashMap<Integer, Integer> noDifSpecies = new HashMap<Integer, Integer>();
            ArrayList<Integer> noUser = new ArrayList<Integer>();
            String photoId = "";
            try {
                query = "SELECT animal_id, photo_id, person_id, species FROM animal LIMIT " + (currentIndex) + ",1"; //accessed by sort type none*****
                rs = state.executeQuery(query);
                String animalId = "";
                String personId = "";
                String species = "";
                while (rs.next()) //process next entry
                {
                    animalId = rs.getString("animal_id");
                    photoId = rs.getString("photo_id");
                }
                //System.out.println(animalId);
                boolean photoIDExists = false;
                for (Iterator<Integer> it = photoClassified.iterator(); it.hasNext(); ) {
                    Integer f = it.next();
                    if (f.equals(Integer.parseInt(photoId)))
                        photoIDExists = true;
                        //System.out.println("---------------------------------");
                        //System.out.println("Duplicate classification avoided.");
                        //System.out.println("---------------------------------");
                        break;
                }
                String getPhotoInstances = "SELECT animal_id, species, person_id FROM animal WHERE photo_id =" + photoId;
                ResultSet photoInstances = state.executeQuery(getPhotoInstances);
                if(!photoIDExists) {
                    photoClassified.add(Integer.parseInt(photoId));

                    while (photoInstances.next()) {
                        animalId = photoInstances.getString("animal_id"); //get all animals regarding the same photo_id.
                        personId = photoInstances.getString("person_id");
                        //photoId=photoInstances.getString("photo_id");
                        // personId=photoInstances.getString("personId"); //get user associated with photo_id then add to anarraylist.

                        species = photoInstances.getString("species");

                        if (Integer.parseInt(species) == 86) {
                            noUser.add(Integer.parseInt(personId));
                            fractionalBlank = true;
                        }
                        if (Integer.parseInt(species) == 87) {
                            //Human in photo
                        }
                        if (noDifSpecies.get(Integer.parseInt(species)) != null) {
                            noDifSpecies.put(Integer.parseInt(species), noDifSpecies.get(Integer.parseInt(species)) + 1);
                            total += 1; //get total while processing for evenness
                        } else {
                            noDifSpecies.put(Integer.parseInt(species), 1);
                            total += 1;
                        }
                    }

                    double result = calculateEvenness(noDifSpecies, total); //each individual photo.
                    boolean classified = false;
                    boolean majorityBlanks = false;
                    if (result < 0.5) //testing value needs be able to be changed by admin
                    {
                        int maxValueInMap=(Collections.max(noDifSpecies.values()));  //ensure not to report the user if the majority of cases are nothing there photos.
                        for (HashMap.Entry<Integer, Integer> entry : noDifSpecies.entrySet()) {
                            if (entry.getValue()==maxValueInMap) {
                               if(entry.getKey() == 86) {
                                   majorityBlanks = true;
                               }
                            }
                        }
                        classified = true;
                        System.out.println("");
                        String temp = "Image classified: " + photoId.toString() + " Evenness value: " + result;
                        System.out.println(temp);
                        System.out.println("");
                    }
                    if (fractionalBlank && (classified == true) && (result != 0) && (!majorityBlanks)) //if 1.0 then 50/50 avoid flag only if classified passed
                    {
                        for (int i = 0; i < noUser.size(); i++) {
                            //make new table adding a point with user_id let admin know if image has been classified and someone has said nothing is there.
                            //could also be used if the user has selected the wrong species to many times etc...
                            String temp = "User flag point added:" + noUser.get(i).toString();
                            System.out.println(temp);
                        }
                    } else {
                        //recirculate image priority?
                    }
                }

                }catch(SQLException ex){
                    return false;
                }
            currentIndex++; //increase where up to, this restarts every time the algorithm is re ran**.
        }

        return true;
    }
    public static void main(String[] args)
    {
        SQLLoader loader = new SQLLoader();
        boolean accept = loader.classifyImages();
        if(accept)
        {
            System.out.println("True");
        }
    }
}
