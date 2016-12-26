/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.data.exceptions;

/**
 *
 * @author John
 */
public class NoJobsAvailableException extends Exception{

    public NoJobsAvailableException(String message) {
        super(message);
    }

    public NoJobsAvailableException(String message, Throwable cause) {
        super(message, cause);
    }
    
}
