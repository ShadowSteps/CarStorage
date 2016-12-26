/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.webapi.controller;

import com.shadows.carstorage.job.schedular.data.repository.FileJobRepository;
import com.shadows.carstorage.job.schedular.data.repository.IJobRepository;
import com.shadows.carstorage.job.schedular.webapi.config.GlobalConfiguration;
import java.io.IOException;


public class BaseAPIController {
    protected IJobRepository jobRepository;

    public BaseAPIController() throws IOException {
        this.jobRepository = new FileJobRepository(GlobalConfiguration.getDataFolder());
    }
    
}
