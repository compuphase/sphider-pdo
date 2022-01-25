# sphider-pdo

The standard distribution of Sphider, a search engine in PHP, requires MySQL. This version is ported 
to use PHP Data Objects (PDO), so that it can be used with many database back-ends, including SQLite.

In general, Sphider-PDO is just a basic port of Sphider version 1.3.5 (by Ando Saabas) to use the PDO interface to 
talk to databases. Most of the Sphider documentation on configuration and indexing options still 
applies; please see the original Sphider site for that documentation. Documentation specific to 
this port is in the [Wiki](https://github.com/compuphase/sphider-pdo/wiki) of this project.

Instructions for installation and configuration are in the [Wiki](https://github.com/compuphase/sphider-pdo/wiki).

Notable changes from Sphider 1.3.5:
 * The biggest change is invisible to the user: the database back-end now uses PDO and is no longer hard-linked to MySQL. 
 * The section for the database maintenance and backup in the "Admin" interface is available only for MySQL, though. The SQL queries are highly database-specific in this area. (PDO provides data-access abstraction, not database abstraction.) 
 * Support for non-English languages has improved; accented characters are now supported. However, Sphider-PDO is still dependent on the Latin-1 encoding. There is limited support for multilingual sites. 
 * The interface for "Did you mean?" search terms has changed and been extended.
 
The original release of Sphider (by Ando Saabas) is now at 1.3.6. The fix that this update brings to 1.3.5 has been implemented in Sphider-PDO as well.
