/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.plugin.core;

/**
 *
 * @author John
 */
public class Job {
    private final String Type;
    private final String Url;

    public Job(String Type, String Url) {
        this.Type = Type;
        this.Url = Url;
    }

    public String getType() {
        return Type;
    }

    public String getUrl() {
        return Url;
    }   
}
