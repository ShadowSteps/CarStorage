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
public class Error extends JSONObject{
    private final String Message;
    private final String ExceptionType;

    public Error(String Message, String ExceptionType) {
        this.Message = Message;
        this.ExceptionType = ExceptionType;
    }

    public String getMessage() {
        return Message;
    }

    public String getExceptionType() {
        return ExceptionType;
    }
    
    
}
