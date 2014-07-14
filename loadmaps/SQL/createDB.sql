CREATE TABLE IF NOT EXISTS judge(
    judge_id INT(1) AUTO_INCREMENT,
    judge_name VARCHAR(20) NOT NULL,
    judge_email VARCHAR(30) NOT NULL UNIQUE,
    PRIMARY KEY (judge_id)
);

CREATE TABLE IF NOT EXISTS competitor(
    competitor_id INT(1) AUTO_INCREMENT,
    competitor_name VARCHAR(20) NOT NULL,
    competitor_password VARCHAR(64) NOT NULL,
    competitor_email VARCHAR(30) NOT NULL UNIQUE,
    competitor_birthdate DATE NOT NULL,
    competitor_sex VARCHAR(1),
    CONSTRAINT sex_invalid CHECK (competitor_sex = 'M' OR competitor_sex = 'F'),
    PRIMARY KEY (competitor_id)
);

CREATE TABLE IF NOT EXISTS competitor_competitor(
    competitor_id_1 INT(1) REFERENCES competitor(competitor_id) ON DELETE CASCADE,
    competitor_id_2 INT(1) REFERENCES competitor(competitor_id) ON DELETE CASCADE,
    PRIMARY KEY(competitor_id_1, competitor_id_2)
);

CREATE TABLE IF NOT EXISTS event(
    event_id INT(1) AUTO_INCREMENT,
    competitor_id INT(1) REFERENCES competitor(competitor_id) ON DELETE CASCADE,
    event_password VARCHAR(64),
    event_name VARCHAR(20) NOT NULL,
    event_begin_date DATE,
    event_place VARCHAR(30),
    event_privacity TINYINT(1) DEFAULT 0,
    event_description VARCHAR(80),
    PRIMARY KEY(event_id)
);

CREATE TABLE IF NOT EXISTS judge_event(
    judge_id INT(1) REFERENCES judge(judge_id) ON DELETE CASCADE,
    event_id INT(1) REFERENCES event(event_id) ON DELETE CASCADE,
    PRIMARY KEY(judge_id, event_id)
);

CREATE TABLE IF NOT EXISTS competitor_event(
    event_id INT(1) REFERENCES event(event_id) ON DELETE CASCADE,
    competitor_id INT(1) REFERENCES competitor(competitor_id) ON DELETE CASCADE,
    private BOOLEAN DEFAULT FALSE,
    PRIMARY KEY(event_id, competitor_id)
);

CREATE TABLE IF NOT EXISTS mark(
    event_id INT(1) REFERENCES competitor_event(event_id) ON DELETE CASCADE,
    competitor_id INT(1) REFERENCES competitor_event(competitor_id) ON DELETE CASCADE,
    mark_date DATETIME,
    mark_latitude DOUBLE(10,8) NOT NULL,
    mark_longitude DOUBLE(11,8) NOT NULL,
    PRIMARY KEY(event_id, competitor_id, mark_date)
);

CREATE TABLE IF NOT EXISTS event_invitation(
    event_id INT(1) REFERENCES competitor_event(event_id) ON DELETE CASCADE,
    competitor_id INT(1) REFERENCES competitor_event(competitor_id) ON DELETE CASCADE,
    notification_seen BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (event_id, competitor_id)
);

CREATE TABLE IF NOT EXISTS route_invitation(
    owner_id INT(1) REFERENCES competitor_event(competitor_id) ON DELETE CASCADE,
    competitor_id INT(1) REFERENCES competitor_event(competitor_id) ON DELETE CASCADE,
    route VARCHAR(64),
    notification_seen BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (competitor_id, owner_id, route)
);
