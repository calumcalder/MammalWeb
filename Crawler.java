/**
 * Created by Stefan on 30/01/2016.
 */
/**
 * Created by Stefan on 30/01/2016.
 */
import com.sun.org.apache.xpath.internal.operations.Bool;

import java.sql.*;
import java.util.*;
import java.lang.Math;

public class Crawler {

    public Crawler() {
    }

    static final int ID_NONE = 86;
    static final int ID_HUMAN = 87;

    int currentIndex = 0;
    int currentIndexLimit = 0;

    public Connection connectDataBase() {
        Connection con = null;
        try {
            Class.forName("com.mysql.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            System.out.println(e.getMessage());
        }
        try {
            String host = "jdbc:mysql://127.0.0.1:3306";
            String uName = "";
            String uPass = "";
            con = DriverManager.getConnection(host, uName, uPass);
        } catch (SQLException err) {
            System.out.println(err.getMessage());
        }
        return con;
    }

    private double calculateEvenness(HashMap temp, double total) {
        if (temp.size() == 1) //all agree avoid processing.
        {
            return 0;
        }
        double numerator = 0;
        double evenness = 0;

        for (Object value : temp.values()) {
            numerator += (double) ((Double.parseDouble(value.toString()) / total) * (Math.log(Double.parseDouble(value.toString()) / total)));
        }
        evenness = (-numerator) / Math.log(temp.size());

        return evenness;
    }


    private int getTableSize() {
        int size = 0;
        try {
            Statement count = null;
            count = connectDataBase().createStatement();
            String query = "SELECT COUNT(*) FROM MammalWeb.Animal";
            ResultSet countRs = count.executeQuery(query); //getTableSize needs to be wrapped in a timer every 5 minutes use for checking whether to update or not.
            while (countRs.next()) {
                size = countRs.getInt(1);
            }
        } catch (SQLException err) {
            return 0;
        }
        return size;
    }



    private void classificationUserNormal(ArrayList<Integer> users, int photo_id)
    {
        //write to xclassification
    }

    private void classificationUserBlank(ArrayList<Integer> users, int photo_id)
    {
        //write to xclassification
    }

    private void classificationUserHuman(ArrayList<Integer> users, int photo_id)
    {
        //write to xclassification
    }

    private boolean MajorityBlankCheck()
    {
        return true;
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
            System.out.println("SQL Error: "+ex.getMessage().toString());
            return false;
        }

        System.out.println(currentIndexLimit);


        double total = 0;
        boolean fractionalBlank = false;

        HashMap<Integer, Integer> noDifSpecies = new HashMap<Integer, Integer>();
        ArrayList<Integer> noUser = new ArrayList<Integer>();
        ArrayList<Integer> noUserSpec = new ArrayList<Integer>();
        ArrayList<Integer> noUserHuman = new ArrayList<Integer>();


        try {
            String animalId = "";
            String personId = "";
            String species = "";
            String photoId = "0";
            String previousId = "0";

            int consecBlanksCount = 0;
            int blankConsensusCount = 0;
            double result = 0;
            boolean consecBlanksFlag = false;
            String getPhotoInstances = "SELECT animal_id, species, person_id, photo_id FROM MammalWeb.Animal ORDER BY photo_id";

            ResultSet photoInstances = state.executeQuery(getPhotoInstances);


            while (photoInstances.next()){
                photoId = photoInstances.getString("photo_id");

                if(previousId.equals(photoId)) {

                    animalId = photoInstances.getString("animal_id"); //get all animals regarding the same photo_id.
                    personId = photoInstances.getString("person_id");
                    species = photoInstances.getString("species");

                    if (Integer.parseInt(species) == ID_NONE) {
                        noUser.add(Integer.parseInt(personId));
                        fractionalBlank = true;
                        consecBlanksCount += 1;
                        blankConsensusCount += 1;
                        if(consecBlanksCount == 5)
                        {
                            consecBlanksFlag = true;
                        }
                    }
                    if (Integer.parseInt(species) == ID_HUMAN) {
                        noUserHuman.add(Integer.parseInt(personId));
                    }
                    if (noDifSpecies.get(Integer.parseInt(species)) != null) {
                        noDifSpecies.put(Integer.parseInt(species), noDifSpecies.get(Integer.parseInt(species)) + 1);
                        total += 1; //get total while processing for evenness
                        noUserSpec.add(Integer.parseInt(personId));
                        if(Integer.parseInt(species) != ID_NONE)
                        {
                            consecBlanksCount = 0;
                        }

                    } else if(noDifSpecies.get(Integer.parseInt(species)) == null) {
                        noDifSpecies.put(Integer.parseInt(species), 1);
                        noUserSpec.add(Integer.parseInt(personId));
                        total += 1;
                        if(Integer.parseInt(species) != ID_NONE)
                        {
                            consecBlanksCount = 0;
                        }
                    }


                }
                if(photoInstances.next())
                {
                    if(!photoId.equals(photoInstances.getString("photo_id")))
                    {
                        result = calculateEvenness(noDifSpecies,total);
                        if(consecBlanksFlag && photoId.equals("124"))
                        {
                            classificationUserBlank(noUser,Integer.parseInt(photoId));
                        }
                        if(result < 0.5)
                        {
                            System.out.println("Evenness: " +result+ " Image: " + previousId);
                            if(fractionalBlank)
                            {

                            }
                        }
                        consecBlanksFlag = false;

                        noDifSpecies.clear();
                        noUser.clear();
                        noUserSpec.clear();
                        noUserHuman.clear();
                        blankConsensusCount = 0;
                        consecBlanksCount = 0;
                        total=0;

                    }
                    previousId = photoInstances.getString("photo_id");
                    photoInstances.previous();
                }


            }
            System.out.println("Evenness: " +result+ " Image: " + previousId);
        }catch(SQLException ex){
            System.out.println("SQL Error: "+ex.getMessage().toString());
            return false;
        }

        return true;
    }
    public static void main(String[] args)
    {
        Crawler loader = new Crawler();
        boolean accept = loader.classifyImages();
        if(accept)
        {
            System.out.println("True");
        }
    }
}
