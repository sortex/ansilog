CREATE TABLE "<?php echo $table_name; ?>"
(
	timestamp varchar(14) NOT NULL,
	description varchar(100) NOT NULL,
	"group" varchar(100) NOT NULL,
	applied boolean DEFAULT false,
	CONSTRAINT <?php echo $table_name; ?>_pkey PRIMARY KEY (timestamp, "group"),
	CONSTRAINT <?php echo $table_name; ?>_ukey UNIQUE (timestamp, description)
)
