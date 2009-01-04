#!/bin/bash

mysql -p < table_setup.sql

for i in ../profile/*; do
	rm $i;
done;
