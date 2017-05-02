@echo off
cd Job.Scheduler.WebAPI
php composer.phar self-update
php composer.phar update

cd ..\search_project
php composer.phar self-update
php composer.phar update

cd ..\Job.Crawler
php composer.phar self-update
php composer.phar update

cd ..