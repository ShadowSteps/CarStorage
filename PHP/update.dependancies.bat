@echo off
cd Job.Core
php composer.phar self-update
php composer.phar update

cd ../Job.Scheduler.Data.Interfaces
php composer.phar self-update
php composer.phar update

cd ../Job.Scheduler.Data.Postgres
php composer.phar self-update
php composer.phar update

cd ../Job.Scheduler.WebAPI
php composer.phar self-update
php composer.phar update

cd ..