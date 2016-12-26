/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.webapi.controller;

import com.shadows.carstorage.job.core.communication.JSONObject;
import com.shadows.carstorage.job.core.communication.Job;
import com.shadows.carstorage.job.core.communication.JobRegistration;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RestController;
import com.shadows.carstorage.job.core.communication.Error;
import com.shadows.carstorage.job.core.communication.JobStatus;
import com.shadows.carstorage.job.schedular.data.exceptions.NoJobsAvailableException;
import com.shadows.carstorage.job.schedular.data.repository.IJobRepository;
import java.io.IOException;
import org.springframework.http.HttpStatus;

@RestController
@RequestMapping("/job")
public class JobController extends BaseAPIController{

    public JobController() throws IOException {
        super();
    }
    
    @RequestMapping(value = "/register", method = RequestMethod.POST)    
    public ResponseEntity<JSONObject> Register(@RequestBody JobRegistration infoRegistration) {
        JSONObject response;
        HttpStatus status = HttpStatus.OK;
        try {
            this.jobRepository.DeleteJobById(infoRegistration.getFinishedJobId());
            for (Job job : infoRegistration.getNewJobs()) {
                this.jobRepository.RegisterNewJob(job.getUrl(), job.getType());
            }   
            response = new JobStatus(true);
        }
        catch(Exception exp) {
            status = HttpStatus.INTERNAL_SERVER_ERROR;
            response = new Error(exp.getMessage(), exp.getClass().getName());
        }
        return new ResponseEntity(response, status);                      
    }
    
    @RequestMapping(value = "/next", method = RequestMethod.GET)    
    public ResponseEntity<JSONObject> GetNextJob() {
        JSONObject response;
        HttpStatus status = HttpStatus.OK;
        try {
            response = jobRepository.GetNextAvailableJob();
        }
        catch(NoJobsAvailableException exp) {
            response = new JobStatus();
        }
        catch(Exception exp) {
            status = HttpStatus.INTERNAL_SERVER_ERROR;
            response = new Error(exp.getMessage(), exp.getClass().getName());
        }
        return new ResponseEntity(response, status);
    }   
}
