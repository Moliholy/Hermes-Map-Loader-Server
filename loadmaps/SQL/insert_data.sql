INSERT INTO competitor(competitor_id, competitor_name, competitor_password, competitor_email, competitor_birthdate,competitor_sex) VALUES
(0,'Jose','mLmTghNCPUHGNdBYyKhjAecCiSNZuI5kuqtnAJjCULY=', 'gaudy41@gmail.com','1988-12-23','M'),
(1,'Manolo','XGNg695nU6YTFgA1h9nJLZYXrV9Sq0lkhYqU4tHaGAM=','manolo@manolo.com','1999-11-04','M');


INSERT INTO event(event_id, competitor_id, event_begin_date, event_name, event_place) VALUES
(0,0,'06-07-2013','nombre prueba','Cazorla'),
(1,0,'01-08-2014','nombre prueba2','Trololo');



INSERT INTO competitor_event(event_id, competitor_id) VALUES
(0,0),
(1,0);

INSERT INTO competitor_competitor(competitor_id_1, competitor_id_2) VALUES
(0,1),
(1,0);
