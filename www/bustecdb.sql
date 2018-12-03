CREATE TABLE transportistas(
  id INT PRIMARY KEY AUTOINCREMENT,
  nombre TEXT,
  edad INT,
  horario TEXT
);

CREATE TABLE transportes(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  condiciones TEXT,
  nodelo TEXT
);

CREATE TABLE rutas(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre TEXT,
  hinicio TEXT,
  hfin TEXT,
  id_transportiste INT references transportistas(id),
  id_transporte INT references transportes(id)
);

CREATE TABLE pasajeros(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre TEXT,
  edad INT,
  id_ruta INT references rutas(id)
);

CREATE TABLE usuarios(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  usuario TEXT,
  pass TEXT,
  rol INT
);
