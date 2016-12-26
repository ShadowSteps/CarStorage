/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.webapi.config;

import java.io.File;
import java.io.FileNotFoundException;

/**
 *
 * @author John
 */
public class GlobalConfiguration {
    private static String dataFolder;
    public static void setDataFolder(String folder) throws FileNotFoundException{        
        dataFolder = folder;       
    }
    
    public static String getDataFolder(){
        return dataFolder;
    }
}
