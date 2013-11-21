This code was created by the Georgetown University Libraries to assist in the management of DSpace.
This code is being shared for illustrative purposes.  The code was refactored to pull out several bits of institution-specific logic.
This code requires significant modification and customization before being deployed in a live setting.

Documentation
* https://docs.google.com/presentation/d/11GujDtJaIJVHChZ36bzUrlzVNqiDO60s0aq6tcf0Bzs/edit?usp=sharing
* Open Repositories 2013 Presentation
* https://docs.google.com/presentation/d/11C0XHY-a594aiKPCKWNs3MlQrHh6lIz8qdWTowiavNo/edit?usp=sharing

Customization Steps
1. Copy phpconfig/init.php.template to phpconfig/init.php, make appropriate edits
2. Create a custom class that overrides customRest.php or customPostgres.php.  Override methods with local values as needed.
3. Apache config
   Web visible: /web
   Web visible with authentication: /auth

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