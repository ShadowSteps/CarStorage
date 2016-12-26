/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.daemon;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.util.concurrent.locks.ReentrantLock;

/**
 *
 * @author John
 */
public class JobGeneratorListener implements ActionListener{
    private final ReentrantLock _lock = new ReentrantLock();
    @Override
    public void actionPerformed(ActionEvent e) {
        if (_lock.tryLock()) {
            try {
                
            }
            finally{
                _lock.unlock();
            }
        }
    }
}
