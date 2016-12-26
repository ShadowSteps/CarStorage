/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.data.repository;

import com.shadows.carstorage.job.core.enums.JobType;
import com.shadows.carstorage.job.schedular.data.exceptions.NoJobsAvailableException;
import com.shadows.carstorage.job.core.communication.Job;
import com.shadows.carstorage.job.schedular.data.exceptions.JobRepositoryException;
/**
 *
 * @author John
 */
public interface IJobRepository {
    public String RegisterNewJob(String url, JobType type) throws JobRepositoryException;
    public void DeleteJobById(String id) throws JobRepositoryException;
    public Job GetNextAvailableJob() throws NoJobsAvailableException, JobRepositoryException;
}
