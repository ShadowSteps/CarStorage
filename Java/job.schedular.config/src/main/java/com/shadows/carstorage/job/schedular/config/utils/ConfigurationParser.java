/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.config.utils;

import com.shadows.carstorage.job.schedular.config.JobSchedularConfiguration;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import javax.xml.bind.JAXBContext;
import javax.xml.bind.JAXBException;
import javax.xml.bind.Unmarshaller;

/**
 *
 * @author John
 */
public class ConfigurationParser {
    public static JobSchedularConfiguration Parse(String configurationPath) throws JAXBException, FileNotFoundException{
        JAXBContext context = JAXBContext.newInstance(JobSchedularConfiguration.class);
        Unmarshaller unMarshaller = context.createUnmarshaller();
        return (JobSchedularConfiguration)unMarshaller.unmarshal(new FileInputStream(configurationPath));
    }
}
