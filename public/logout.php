<?php
session_start();
session_destroy();
header('Location: /myapp/test/public/login');
exit; 