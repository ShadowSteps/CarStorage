/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.core.communication;

import com.shadows.carstorage.job.core.enums.JobType;
import java.io.UnsupportedEncodingException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 *
 * @author John
 */
public class Job extends JobStatus{
    private final JobType type;
    private final String url;
    private String id;

    public Job(JobType type, String url) throws NoSuchAlgorithmException, UnsupportedEncodingException {
        this.hasNewJob = true;
        this.type = type;
        this.url = url;
        makeId();
    }
    
    private void makeId() throws NoSuchAlgorithmException, UnsupportedEncodingException {
        MessageDigest md = MessageDigest.getInstance("SHA-256");
        String text = "This is some text";
        md.update(text.getBytes("UTF-8"));
        byte[] digest = md.digest();
        this.id = String.format("%064x", new java.math.BigInteger(1, digest));
    }

    public JobType getType() {
        return type;
    }

    public String getUrl() {
        return url;
    }
    
    public String getId(){
        return id;
    }
}
