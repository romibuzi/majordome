CREATE TABLE runs(
  id          INTEGER PRIMARY KEY,
  createdAt   TEXT
);

CREATE TABLE rules(
  id          INTEGER PRIMARY KEY,
  name        TEXT,
  description TEXT
);

CREATE TABLE violations(
  id               INTEGER PRIMARY KEY,
  resource_id      TEXT,
  resource_type    TEXT,
  run_id           INTEGER,
  rule_id          INTEGER,

  FOREIGN KEY(run_id) REFERENCES runs(id),
  FOREIGN KEY(rule_id) REFERENCES rules(id)
);
