# slate Database Setup

```shell
# create a new database
sudo -u postgres createdb slate

# build the algae app data model
psql slate postgres -f /opt/algae-main/database/setup_app_database.sql

# add the slate data model
psql slate postgres -f /opt/slate-main/database/setup_slate_database.sql

# check versions
psql slate postgres -c "SELECT algae_app_database_version(), slate_database_version();"
```

```console
 algae_app_database_version | slate_database_version 
----------------------------+------------------------
 2026.08.29                 | 2026.08.29
(1 row)
```
