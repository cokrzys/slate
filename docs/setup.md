## Setup the algae admin Database
```shell
# create algae database, you will have to enter the postgres database password
sudo -u postgres createdb algae

# setup the algae data model in the new database
psql algae postgres -f /opt/algae-main/database/setup_admin_database.sql

# check reading some data
psql algae postgres -c "SELECT name FROM ref.record_status"
```

```console
   name   
----------
 Active
 InActive
(2 rows)
```
