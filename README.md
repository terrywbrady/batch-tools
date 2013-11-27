This code was created by the Georgetown University Libraries to assist in the management of DSpace.
This code is being shared for illustrative purposes.  

---

Documentation
* https://docs.google.com/presentation/d/11GujDtJaIJVHChZ36bzUrlzVNqiDO60s0aq6tcf0Bzs/edit?usp=sharing
* Open Repositories 2013 Presentation
* https://docs.google.com/presentation/d/11C0XHY-a594aiKPCKWNs3MlQrHh6lIz8qdWTowiavNo/edit?usp=sharing

Customization Steps
* Copy phpconfig/init.php.template to phpconfig/init.php, make appropriate edits
* Create a custom class that overrides customPdo.php, customRest.php, or customPostgres.php.  Override methods with local values as needed.
* copy bin-src/* to /bin
* Modify DSPACETOOLSROOT. DSPACEROOT, YOURPFX in bin/dspaceBatch.sh, make the script editable
* Validate/update the filter names in auth/filterMedia.php
* Apache config (limit the app to necessary users or ip addresses)
* -- Web visible: /web
* -- Web visible with authentication: /auth

License information is contained below.

Copyright (c) 2013, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
