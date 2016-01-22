import java.sql.*;
import java.util.*;
import java.lang.Math;

public class SQLLoader {

	
	static final int ID_NONE = 86;
	static final int ID_HUMAN = 87;
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
            String host = "jdbc:mysql://calum-calder.com:3306";
            String uName = "admin3";
            String uPass = "admin";
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
            String query = "SELECT COUNT(*) FROM MammalWeb.Animal";
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
    
    boolean photoClassified(int curID, Set<Integer> classified) {
	for (int id : classified.iterator()) {
		if (id == curID) 
			return true;
	}
	return false;
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
	System.out.println(currentIndex);
	System.out.println(currentIndexLimit);
        while(currentIndex != currentIndexLimit) {
            double total = 0;
            boolean fractionalBlank = false;
            HashMap<Integer, Integer> noDifSpecies = new HashMap<Integer, Integer>();
            ArrayList<Integer> noUser = new ArrayList<Integer>();
            ArrayList<Integer> noUserSpec = new ArrayList<Integer>();
            String photoId = "";
            try {
                query = "SELECT animal_id, photo_id, person_id, species FROM MammalWeb.Animal LIMIT " + (currentIndex) + ",1"; //accessed by sort type none*****
                rs = state.executeQuery(query);
                String animalId = "";
                String personId = "";
                String species = "";
		// Will this not only find the last entry in the result set?
                while (rs.next()) //process next entry
                {
                    animalId = rs.getString("animal_id");
                    photoId = rs.getString("photo_id");
                }
                //System.out.println(animalId);
		boolean photoIDExists = photoClassified(photoId, photoClassified);
		
                String getPhotoInstances = "SELECT animal_id, species, person_id FROM MammalWeb.Animal WHERE photo_id =" + photoId;
                ResultSet photoInstances = state.executeQuery(getPhotoInstances);
                if(!photoIDExists) {
                    photoClassified.add(Integer.parseInt(photoId));

                    while (photoInstances.next()) {
                        animalId = photoInstances.getString("animal_id"); //get all animals regarding the same photo_id.
                        personId = photoInstances.getString("person_id");
                        //photoId=photoInstances.getString("photo_id");
                        // personId=photoInstances.getString("personId"); //get user associated with photo_id then add to anarraylist.

                        species = photoInstances.getString("species");

                        if (Integer.parseInt(species) == ID_NONE) {
                            noUser.add(Integer.parseInt(personId));
                            fractionalBlank = true;
                        }
                        if (Integer.parseInt(species) == ID_HUMAN) {
                            //Human in photo
                        }
                        if (noDifSpecies.get(Integer.parseInt(species)) != null) {
                            noDifSpecies.put(Integer.parseInt(species), noDifSpecies.get(Integer.parseInt(species)) + 1);
                            total += 1; //get total while processing for evenness
                            noUserSpec.add(Integer.parseInt(personId));
                        } else {
                            noDifSpecies.put(Integer.parseInt(species), 1);
                            noUserSpec.add(Integer.parseInt(personId));
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
                               if(entry.getKey() == ID_NONE) {
                                   majorityBlanks = true;
                               }
                            }
                        }

                        if(majorityBlanks == false) {
                            //write to database noUserSpec with photo id to xclassification table
                        }
                        else if(majorityBlanks == true) {
                            //write to database noUser with photo id to xclassification table
                        }
                        classified = true;
                        System.out.println("");
                        String temp = "Image classified: " + photoId.toString() + " Evenness value: " + result;
                        System.out.println(temp);
                        System.out.println("");
                    }
                    if (fractionalBlank && (classified == true) && (result != 0) && (!majorityBlanks)) //if classified passed
                    {
                        for (int i = 0; i < noUser.size(); i++) {
                            //report user to seperate table with spamming point.
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
