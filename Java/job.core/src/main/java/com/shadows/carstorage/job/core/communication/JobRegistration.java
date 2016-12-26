/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.core.communication;

import java.util.ArrayList;

/**
 *
 * @author John
 */
public class JobRegistration extends JSONObject {
    private final String finishedJobId;
    private final ArrayList<Job> newJobs;

    public JobRegistration(String finishedJobId) {
        this.finishedJobId = finishedJobId;
        this.newJobs = new ArrayList<>();
    }

    public JobRegistration(String finishedJobId, ArrayList<Job> newJobs) {
        this.finishedJobId = finishedJobId;
        this.newJobs = newJobs;
    }

    public String getFinishedJobId() {
        return finishedJobId;
    }

    public ArrayList<Job> getNewJobs() {
        return newJobs;
    }
   
}
