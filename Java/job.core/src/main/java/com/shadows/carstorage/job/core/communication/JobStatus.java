/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.core.communication;

/**
 *
 * @author John
 */
public class JobStatus extends JSONObject{
    protected boolean hasNewJob = false;

    public boolean isHasNewJob() {
        return hasNewJob;
    }

    public JobStatus() {
    }

    public JobStatus(boolean hasNewJob) {
        this.hasNewJob = hasNewJob;
    }        
    
}
