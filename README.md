# Centos Web Panel Server Manager Extension For Boxbilling

> Licence

- Centos Web Panel Server Manager For BoxBilling http://www.boxbilling.com/
- This source file is subject to the license that is bundled with this package in the file LICENSE.txt
- Created by Grant Bamford https://www.speeddemon.co.za/
- Version 0.8 (6/8/2020) 

> Installation

- Download Cwp.php file
- Upload to your Boxbilling Installation using the following path : bb-library -> Server -> Manager
- Create An Authorisation Key on CWP and enable all account permissions 
- Login to your BoxBilling admin section, create a new server with the Centos Web Panel manager
- Paste your key code in the field marked 'Access Hash'
- Set the port number to 2304 and enable secure connection
- Create a new package with the newly created server profile
- Create 4 additonal fields under 'Server manager specific parameters' as per below
- Save and test connection.

> Server manager specific parameters

- packageid (This is the id of the matching package you have set up on CWP)
- inode (No. inode)
- limit_nproc (No. limit_nproc)
- limit_nofile (No. limit_nofile)
