/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.plugin.core;

import java.io.IOException;
import java.util.ArrayList;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import org.w3c.dom.Document;
import org.xml.sax.SAXException;

/**
 *
 * @author John
 */
abstract public class AJobGenerator {
    private DocumentBuilder builder = null;
    public AJobGenerator() throws ParserConfigurationException {
        DocumentBuilderFactory builderFactory = DocumentBuilderFactory.newInstance();
        builder = builderFactory.newDocumentBuilder();
    }
           
    abstract protected ArrayList<Job> ExtractPagesFromDocument(Document document);
    abstract protected ArrayList<Job> ExtractJobsFromDocument(Document document);    
    
    public ArrayList<Job> GetAllPageJobs(String mainUrl) throws SAXException, IOException {
        Document initialDocument = builder.parse(mainUrl);
        return ExtractPagesFromDocument(initialDocument);              
    }
    
    public ArrayList<Job> GetAllContentJobs(String pageUrl) throws SAXException, IOException {
        Document pageDocument = builder.parse(pageUrl);
        return ExtractJobsFromDocument(pageDocument);              
    }
}
