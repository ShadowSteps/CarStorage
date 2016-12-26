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
public class JobRepositoryException extends Exception{

    public JobRepositoryException(String message) {
        super(message);
    }

    public JobRepositoryException(String message, Throwable cause) {
        super(message, cause);
    }
    
}
