import java.io.*;



import org.apache.tika.Tika;

import org.apache.tika.metadata.Metadata;

import org.apache.tika.parser.AutoDetectParser;

import org.apache.tika.parser.ParseContext;

import org.apache.tika.parser.html.BoilerpipeContentHandler;

import org.apache.tika.parser.html.HtmlParser;

import org.apache.tika.sax.BodyContentHandler;

import org.xml.sax.ContentHandler;



public class Big {

    public static void main(String[] args) throws Exception {

        File folder = new File("NYD3");

        File[] listOfFiles = folder.listFiles();

        StringBuffer sb = new StringBuffer();

        for (int i = 0; i < listOfFiles.length; i++) {

            if (listOfFiles[i].isFile()) {

                File file = listOfFiles[i];



                //File file = new File("0a0f0c7c76b2bc154cf887cfb29262217428e6930a085683985ada659dbf54e1.html");

//            BodyContentHandler handler = new BodyContentHandler();

//            Metadata metadata = new Metadata();

                FileInputStream inputstream = new FileInputStream(file);

//            ParseContext pcontext = new ParseContext();

//            //Html parser

//            HtmlParser htmlparser = new HtmlParser();

//            htmlparser.parse(inputstream, handler, metadata,pcontext);

//            System.out.println("Contents of the document:" + handler.toString());

                AutoDetectParser parser = new AutoDetectParser();

                ContentHandler textHandler = new BodyContentHandler();

                Metadata xmetadata = new Metadata();

                parser.parse(inputstream, new BoilerpipeContentHandler(textHandler), xmetadata);

                sb.append(textHandler.toString());

                System.out.println(i);



            }

            BufferedWriter writer;

            try {

                writer = new BufferedWriter(new FileWriter("big3.txt"));

                writer.write(sb.toString());



                writer.close();



            } catch (IOException e) {

                // TODO Auto-generated catch block

                e.printStackTrace();

            }

        }

    }

}