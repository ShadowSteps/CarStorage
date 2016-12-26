/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.shadows.carstorage.job.schedular.daemon;

import java.util.Scanner;
import javax.swing.Timer;
import org.apache.commons.daemon.Daemon;
import org.apache.commons.daemon.DaemonContext;

/**
 *
 * @author John
 */
public class JobGeneratorDaemon implements Daemon {
    private static Timer timer = null;
    private static final JobGeneratorDaemon jobGeneratorDaemon = new JobGeneratorDaemon();

    public static void main(String[] args) {
        jobGeneratorDaemon.initialize();

        Scanner sc = new Scanner(System.in);
        // wait until receive stop command from keyboard
        System.out.printf("Enter 'stop' to halt: ");
        while(!sc.nextLine().toLowerCase().equals("stop"));
        
        if (timer.isRunning()) {
            jobGeneratorDaemon.terminate();
        }
    }

    public static void windowsService(String args[]) {
        String cmd = "start";
        if (args.length > 0) {
            cmd = args[0];
        }
        if ("start".equals(cmd)) {
            jobGeneratorDaemon.windowsStart();
        }
        else {
            jobGeneratorDaemon.windowsStop();
        }
    }

    public void windowsStart() {
        initialize();
        while (timer.isRunning()) {
            // don't return until stopped
            synchronized(this) {
                try {
                    this.wait(60000);  // wait 1 minute and check if stopped
                }
                catch(InterruptedException ie){}
            }
        }
    }

    public void windowsStop() {
        terminate();
        synchronized(this) {
            // stop the start loop
            this.notify();
        }
    }

    @Override
    public void init(DaemonContext arg0) throws Exception {
    }

    @Override
    public void start() {
        initialize();
    }

    @Override
    public void stop() {
        terminate();
    }

    @Override
    public void destroy() {
    }

    private void initialize() {
        if (timer == null) {
            timer = new Timer(60000, new JobGeneratorListener());
        }
        timer.start();
    }

    public void terminate() {
        if (timer != null) {
            timer.stop();
        }
    }
}
