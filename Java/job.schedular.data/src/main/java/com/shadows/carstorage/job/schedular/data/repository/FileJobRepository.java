/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.data.repository;

import com.shadows.carstorage.job.core.communication.Job;
import com.shadows.carstorage.job.core.enums.JobType;
import com.shadows.carstorage.job.schedular.data.exceptions.JobRepositoryException;
import com.shadows.carstorage.job.schedular.data.exceptions.NoJobsAvailableException;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.io.FilenameUtils;
/**
 *
 * @author John
 */
public class FileJobRepository implements IJobRepository {
    private String storageFilePath;
    private String indexFilePath;
    private String locksFilePath;
    
    private void AddJob(Job job) throws IOException, ClassNotFoundException {
        Map<String, Job> jobsList = GetJobsList();
        if (jobsList.containsKey(job.getId())){
            jobsList.replace(job.getId(), job);
        } else {
            jobsList.put(job.getId(), job);
            List<String> indexList = GetIndexList();
            indexList.add(job.getId());
            SaveIndexList(indexList);
        }                  
        SaveJobsList(jobsList);
        
    }
    
    private void RemoveJob(String id) throws IOException, ClassNotFoundException {
        Map<String, Job> jobsList = GetJobsList();
        if (jobsList.containsKey(id)){
            jobsList.remove(id);
            List<String> indexList = GetIndexList();
            int index = indexList.indexOf(id);
            if (index != -1)
                indexList.remove(index);
            List<String> locks = GetLockList();
            int indexLock = locks.indexOf(id);
            if (indexLock != -1)
                locks.remove(indexLock);
            SaveLockList(indexList);
            SaveIndexList(indexList);
            SaveJobsList(jobsList);
        }                  
    }
    
    private Job GetNextJob() throws IOException, ClassNotFoundException, NoJobsAvailableException {
        List<String> indexList = GetIndexList();
        Map<String, Job> jobsList = GetJobsList();
        if (jobsList.isEmpty())
            throw new NoJobsAvailableException("No jobs found!");
        Job nextJob = null;
        List<String> locks = GetLockList();
        for (String jobId : indexList) {
            if (!locks.contains(jobId)){
                nextJob = jobsList.get(jobId);
                break;
            }
        }
        if (nextJob == null)
            throw new NoJobsAvailableException("All jobs are locked!");
        locks.add(nextJob.getId());
        SaveLockList(locks);
        return nextJob;
    }
    
    private void SaveObject(Object object, String path) throws IOException {
        try (FileOutputStream stream = new FileOutputStream(path)){
            try (ObjectOutputStream outputStream = new ObjectOutputStream(stream)){
                outputStream.writeObject(object);
            }
        }
    }
    
    private <T> T GetObject(String path) throws IOException, ClassNotFoundException {
        T list;
        try (FileInputStream stream = new FileInputStream(storageFilePath)){
            try (ObjectInputStream inputStream = new ObjectInputStream(stream)){
                list = (T)inputStream.readObject();
            }
        }        
        return list;
    }
    
    private Map<String, Job> GetJobsList() throws IOException, ClassNotFoundException {        
        return GetObject(storageFilePath);
    }
    
    private List<String> GetIndexList() throws IOException, ClassNotFoundException{       
        return GetObject(indexFilePath);
    }
    
    private List<String> GetLockList() throws IOException, ClassNotFoundException{       
        return GetObject(locksFilePath);
    }
    
    private void SaveJobsList(Map<String, Job> list) throws IOException {
        SaveObject(list, storageFilePath);
    }
    
    private void SaveIndexList(List<String> list) throws IOException {
        SaveObject(list, indexFilePath);
    }
    
    private void SaveLockList(List<String> list) throws IOException {
        SaveObject(list, locksFilePath);
    }
    
    public FileJobRepository(String DataFolder) throws FileNotFoundException, IOException {
        File directory = new File(DataFolder);
        if (!directory.exists()||!directory.isDirectory())
            throw new FileNotFoundException("Direcotry given does not exist or is invalid!");      
        this.storageFilePath = FilenameUtils.concat(DataFolder, "storage.dat");
        this.indexFilePath = FilenameUtils.concat(DataFolder, "index.dat");
    }
    
    @Override
    public String RegisterNewJob(String url, JobType type) throws JobRepositoryException{
        try {
            Job job = new Job(type, url);
            AddJob(job);
            return job.getId();
        } 
        catch (Exception exp) {
            throw new JobRepositoryException("Could not add job to stogare!", exp);
        }
    }
    
    @Override
    public void DeleteJobById(String id) throws JobRepositoryException {
        try {            
            RemoveJob(id);
        } 
        catch (Exception exp) {
            throw new JobRepositoryException("Could not add job to stogare!", exp);
        }
    }
    
    @Override
    public Job GetNextAvailableJob() throws NoJobsAvailableException, JobRepositoryException {
        try {            
            return GetNextJob();
        } 
        catch (IOException|ClassNotFoundException exp) {
            throw new JobRepositoryException("Could not add job to stogare!", exp);
        }
    }
}
