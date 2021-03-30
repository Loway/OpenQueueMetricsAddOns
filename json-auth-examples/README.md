# Examples to test JSON auth in QM 21.04+

**Please DO NOT use real passwords to access these filees, as data is sent to GitHub.**


Basically, these are "canned" responses, so you can use them "as if" they were aervers always replying the same response. 

For example, to set up an external JSON auth that is always successful, you could add the following lines to your QueueMetrics configuration:

	auth.externalSource=json
	auth.jsonServerUrl=https://raw.githubusercontent.com/Loway/OpenQueueMetricsAddOns/master/json-auth-examples/auth-ok-successful.json
	auth.jsonPost=false
	auth.verboseLog=true


Please note that:

- A **successful** auth requires a user with the same login to exist in QM, it will be used to fetch class/key data
- An **authoritative** auth will create the user if it does not exist
- A **forbidden** auth will just fail
- A **delegated** auth will ask QM to try its local database to see if a user with the gicen password exists; if so, it is used; if not, authentication fails.


