<?php

echo $class.' ['.$e->getCode().']: '.$message."\n";
echo Kohana_Exception::print_trace($trace);
