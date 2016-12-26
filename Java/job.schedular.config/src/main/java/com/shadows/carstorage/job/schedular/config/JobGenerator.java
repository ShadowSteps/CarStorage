/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.config;

import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlType;

/**
 *
 * @author John
 */
@XmlAccessorType(XmlAccessType.FIELD)
@XmlType(propOrder = {"url", "generatorJarPath"})
public class JobGenerator {
    @XmlElement(name = "url", required = true)
    public String url;
    @XmlElement(name="generatorJarPath", required = true)
    public String generatorJarPath;
}
