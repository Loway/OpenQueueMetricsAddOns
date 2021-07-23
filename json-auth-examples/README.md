# Examples to test JSON auth in QM 21.04+

**Please DO NOT use real passwords to access these files, as data is sent to GitHub.**


Basically, these are "canned" responses, so you can use them "as if" they were servers always replying the same response. 

For example, to set up an external JSON auth that is always successful, you could add the following lines to your QueueMetrics configuration:

	auth.externalSource=json
	auth.jsonServerUrl=https://raw.githubusercontent.com/Loway/OpenQueueMetricsAddOns/master/json-auth-examples/auth-ok-successful.json
	auth.jsonPost=false
	auth.verboseLog=true


Please note that:

- A **successful** auth requires a user with **the same login** to exist in QM, it will be used to fetch class/key data
- An **authoritative** auth will create the user if it does not exist with supplied class and key data
- A **forbidden** auth will just fail
- A **delegated** auth will ask QM to try its local database to see if a user with the given password exists; if so, it is used; if not, authentication fails.

Usually the login used in QM is the same as it was entered; the webservice can also specify a `login` attribute that, when present, forces subsequent operations to happen on a specific QM login:

- The file **successful_newlogin** will use login "demouser" in QM 
- The file **authoritative_newlogin** will create user "Agent/12345678" in QM
- The file **delegated_newlogin** will delegate authentication to user "Agent/101" with the password you just entered.




