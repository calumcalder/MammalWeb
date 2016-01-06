import java.sql.*;
import java.util.*;
import java.lang.Math;

public class SQLLoader {

    public  SQLLoader()
    {

    }
    int currentIndex = 1;
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
            if((Integer)value == 86) //86 is the value defined as nothing, see object table.
            {
                //do something to flag user. likely need an array to hold users in.
            }
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
        while(currentIndex != currentIndexLimit) {
            double total = 0;
            HashMap<Integer, Integer> noDifSpecies = new HashMap<Integer, Integer>();
            try {
                query = "SELECT animal_id, photo_id, person_id, species FROM animal LIMIT " + currentIndex; //accessed by sort type none*****
                rs = state.executeQuery(query);
                String animalId = "";
                String photoId = "";
                String personId = "";
                String species = "";
                while (rs.next()) //process next entry
                {
                    animalId = rs.getString("animal_id");
                    photoId = rs.getString("photo_id");
                    personId = rs.getString("person_id");
                    species = rs.getString("species");
                }
                System.out.println(animalId);

                String getPhotoInstances = "SELECT animal_id, species FROM animal WHERE photo_id =" + photoId;
                ResultSet photoInstances = state.executeQuery(getPhotoInstances);
                while (photoInstances.next()) {
                    animalId = photoInstances.getString("animal_id"); //get all animals regarding the same photo_id.
                    //photoId=photoInstances.getString("photo_id");
                    // personId=photoInstances.getString("personId"); //get user associated with photo_id then add to anarraylist.
                    species = photoInstances.getString("species");
                    if (noDifSpecies.get(Integer.parseInt(species)) != null) {
                        noDifSpecies.put(Integer.parseInt(species), noDifSpecies.get(Integer.parseInt(species)) + 1);
                        total+=1; //get total while processing for evenness
                    } else {
                        noDifSpecies.put(Integer.parseInt(species), 1);
                        total+=1;
                    }
                }
            } catch (SQLException ex) {
                return false;
            }
            calculateEvenness(noDifSpecies,total); //each individual photo.
            //todo here setup up threshold then write to a new table classified image etc.
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
