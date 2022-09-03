CREATE TABLE IF NOT EXISTS runs(
  id          INTEGER PRIMARY KEY,
  createdAt   TEXT
);

CREATE TABLE IF NOT EXISTS rules(
  id          INTEGER PRIMARY KEY,
  name        TEXT,
  description TEXT
);

CREATE TABLE IF NOT EXISTS violations(
  id               INTEGER PRIMARY KEY,
  resource_id      TEXT,
  resource_type    TEXT,
  run_id           INTEGER,
  rule_id          INTEGER,

  FOREIGN KEY(run_id) REFERENCES runs(id),
  FOREIGN KEY(rule_id) REFERENCES rules(id)
);
