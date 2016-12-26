/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.webapi.config;

import javax.annotation.PostConstruct;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.ComponentScan;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.web.servlet.config.annotation.EnableWebMvc;
@Configuration 
@ComponentScan("com.shadows.carstorage") 
@PropertySource(value = { "classpath:application-carstorage.properties"})
@EnableWebMvc 
public class AppConfig {
    @Value("${carstorage.data.folder}")
    public String DataStorage;
    
    @PostConstruct
    public void init() {      
        try {
            GlobalConfiguration.setDataFolder(DataStorage);
        }
        catch(Exception exp) {
            
        }
    }
}
