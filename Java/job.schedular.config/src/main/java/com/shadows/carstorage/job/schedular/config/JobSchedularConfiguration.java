/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.config;

import java.util.ArrayList;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlType;

/**
 *
 * @author John
 */

@XmlRootElement(name = "configuration")
@XmlAccessorType(XmlAccessType.FIELD)
@XmlType(propOrder = {})
public class JobSchedularConfiguration {
    @XmlElementWrapper(name = "jobs", required = true)
    @XmlElement(name = "jobGenerator")
    public ArrayList<JobGenerator> jobGenerators;
}
