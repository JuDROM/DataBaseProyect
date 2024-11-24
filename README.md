ALTER USER postgres PASSWORD 'postgres';
-- Tabla docentes
CREATE TABLE docentes (
    cod_doc VARCHAR(10) PRIMARY KEY,
    nomb_doc VARCHAR(50) NOT NULL,
    clave VARCHAR(50) NOT NULL
);

-- Tabla estudiantes
CREATE TABLE estudiantes (
    cod_est VARCHAR(10) PRIMARY KEY,
    nomb_est VARCHAR(50) NOT NULL,
    clave VARCHAR(50) NOT NULL
);

-- Tabla cursos
CREATE TABLE cursos (
    cod_cur VARCHAR(10) PRIMARY KEY,
    nomb_cur VARCHAR(50) NOT NULL,
    cod_doc VARCHAR(10),
    CONSTRAINT fk_cursos_docentes
        FOREIGN KEY (cod_doc) REFERENCES docentes(cod_doc)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- Tabla semestre (cursoSemestres en el código original)
CREATE TABLE cursoSemestres (
    cod_cur VARCHAR(10) NOT NULL,
    year INT CHECK (year > 0),
    periodo INT CHECK (periodo >= 1 AND periodo <= 2),
    PRIMARY KEY (cod_cur, year, periodo),
    CONSTRAINT fk_semestres_cursos
        FOREIGN KEY (cod_cur) REFERENCES cursos(cod_cur)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Tabla inscripciones
CREATE TABLE inscripciones (
    cod_cur VARCHAR(10),
    cod_est VARCHAR(10),
    year INT,
    periodo INT CHECK (periodo >= 1 AND periodo <= 2),
    PRIMARY KEY (cod_cur, cod_est, year, periodo),
    CONSTRAINT fk_inscripciones_cursossu
        FOREIGN KEY (cod_cur, year, periodo) REFERENCES cursoSemestres(cod_cur, year, periodo)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_inscripciones_estudiantesCampo 
        FOREIGN KEY (cod_est) REFERENCES estudiantes(cod_est)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Tabla notas
CREATE TABLE notas (
    nota SERIAL PRIMARY KEY,
    desc_nota VARCHAR(50),
    porcentaje DECIMAL(5, 2),
    posicion INT CHECK (posicion > 0),
    cod_cur VARCHAR(10),
    year INT CHECK (year > 0),
    periodo INT CHECK (periodo >= 1 AND periodo <= 2),
    CONSTRAINT fk_notas_cursos
        FOREIGN KEY (cod_cur, year, periodo) REFERENCES cursoSemestres(cod_cur, year, periodo)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Tabla calificaciones
CREATE TABLE calificaciones (
    cod_cal SERIAL PRIMARY KEY,
    nota INT,
    valor DECIMAL(3, 2) CHECK (valor >= 0 AND valor <= 5),
    fecha DATE,
    cod_cur VARCHAR(10),
    cod_est VARCHAR(10),
    year INT,
    periodo INT,
    CONSTRAINT fk_calificaciones_notas
        FOREIGN KEY (nota) REFERENCES notas(nota)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_calificaciones_inscripciones
        FOREIGN KEY (cod_cur, cod_est, year, periodo) REFERENCES inscripciones(cod_cur, cod_est, year, periodo)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Función para contar registros en una tabla
CREATE OR REPLACE FUNCTION get_size(nombre TEXT) 
RETURNS NUMERIC AS $$
DECLARE
    nreg_doc INTEGER;
BEGIN
    EXECUTE format('SELECT COUNT(*) FROM %I', nombre) INTO nreg_doc;
    RETURN nreg_doc;
END;
$$ LANGUAGE plpgsql;

-- Trigger para generar automáticamente el código de docentes
CREATE OR REPLACE FUNCTION set_cod_doc() 
RETURNS TRIGGER AS $$
BEGIN
    NEW.cod_doc := 'DOC' || get_size('docentes');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_generar_doc
BEFORE INSERT ON docentes
FOR EACH ROW
EXECUTE FUNCTION set_cod_doc();

-- Trigger para generar automáticamente el código de cursos
CREATE OR REPLACE FUNCTION set_cod_cur() 
RETURNS TRIGGER AS $$
BEGIN
    NEW.cod_cur := 'CUR' || get_size('cursos');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_generar_cur
BEFORE INSERT ON cursos
FOR EACH ROW
EXECUTE FUNCTION set_cod_cur();

-- Función para renumerar códigos de cursos después de eliminaciones
CREATE OR REPLACE FUNCTION renumerar_codigos_cursos() 
RETURNS TRIGGER AS $$
DECLARE
    registro RECORD;
    contador INT := 0;
BEGIN
    FOR registro IN SELECT * FROM cursos ORDER BY cod_cur ASC
    LOOP
        UPDATE cursos SET cod_cur = 'CUR' || contador WHERE cod_cur = registro.cod_cur;
        contador := contador + 1;
    END LOOP;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_renumerar_cursos
AFTER DELETE ON cursos
FOR EACH STATEMENT
EXECUTE FUNCTION renumerar_codigos_cursos();

-- Función para renumerar códigos de docentes después de eliminaciones
CREATE OR REPLACE FUNCTION renumerar_codigos_docentes() 
RETURNS TRIGGER AS $$
DECLARE
    registro RECORD;
    contador INT := 0;
BEGIN
    FOR registro IN SELECT * FROM docentes ORDER BY cod_doc ASC
    LOOP
        UPDATE docentes SET cod_doc = 'DOC' || contador WHERE cod_doc = registro.cod_doc;
        contador := contador + 1;
    END LOOP;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_renumerar_docentes
AFTER DELETE ON docentes
FOR EACH STATEMENT
EXECUTE FUNCTION renumerar_codigos_docentes();
