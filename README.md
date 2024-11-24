DatabaseTest
--
-- PostgreSQL database dump
--

-- Dumped from database version 16.4 (Ubuntu 16.4-0ubuntu0.24.04.2)
-- Dumped by pg_dump version 16.4 (Ubuntu 16.4-0ubuntu0.24.04.2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: get_size(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_size(nombre text) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    nreg_doc INTEGER;
BEGIN
    EXECUTE format('SELECT COUNT(*) FROM %I', nombre) INTO nreg_doc;
    RETURN nreg_doc;
END;
$$;


ALTER FUNCTION public.get_size(nombre text) OWNER TO postgres;

--
-- Name: renumerar_codigos_cursos(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.renumerar_codigos_cursos() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.renumerar_codigos_cursos() OWNER TO postgres;

--
-- Name: renumerar_codigos_docentes(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.renumerar_codigos_docentes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.renumerar_codigos_docentes() OWNER TO postgres;

--
-- Name: set_cod_cur(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.set_cod_cur() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.cod_cur := 'CUR' || get_size('cursos');
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.set_cod_cur() OWNER TO postgres;

--
-- Name: set_cod_doc(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.set_cod_doc() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.cod_doc := 'DOC' || get_size('docentes');
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.set_cod_doc() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: calificaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calificaciones (
    cod_cal integer NOT NULL,
    nota integer,
    valor numeric(3,2),
    fecha date,
    cod_cur character varying(10),
    cod_est character varying(10),
    year integer,
    periodo integer,
    CONSTRAINT calificaciones_valor_check CHECK (((valor >= (0)::numeric) AND (valor <= (5)::numeric)))
);


ALTER TABLE public.calificaciones OWNER TO postgres;

--
-- Name: calificaciones_cod_cal_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.calificaciones_cod_cal_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calificaciones_cod_cal_seq OWNER TO postgres;

--
-- Name: calificaciones_cod_cal_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.calificaciones_cod_cal_seq OWNED BY public.calificaciones.cod_cal;


--
-- Name: cursos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cursos (
    cod_cur character varying(10) NOT NULL,
    nomb_cur character varying(50) NOT NULL,
    cod_doc character varying(10)
);


ALTER TABLE public.cursos OWNER TO postgres;

--
-- Name: cursosemestres; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cursosemestres (
    cod_cur character varying(10) NOT NULL,
    year integer NOT NULL,
    periodo integer NOT NULL,
    CONSTRAINT cursosemestres_periodo_check CHECK (((periodo >= 1) AND (periodo <= 2))),
    CONSTRAINT cursosemestres_year_check CHECK ((year > 0))
);


ALTER TABLE public.cursosemestres OWNER TO postgres;

--
-- Name: docentes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.docentes (
    cod_doc character varying(10) NOT NULL,
    nomb_doc character varying(50) NOT NULL,
    clave character varying(50) NOT NULL
);


ALTER TABLE public.docentes OWNER TO postgres;

--
-- Name: estudiantes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estudiantes (
    cod_est character varying(10) NOT NULL,
    nomb_est character varying(50) NOT NULL,
    clave character varying(50) NOT NULL
);


ALTER TABLE public.estudiantes OWNER TO postgres;

--
-- Name: inscripciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inscripciones (
    cod_cur character varying(10) NOT NULL,
    cod_est character varying(10) NOT NULL,
    year integer NOT NULL,
    periodo integer NOT NULL,
    CONSTRAINT inscripciones_periodo_check CHECK (((periodo >= 1) AND (periodo <= 2)))
);


ALTER TABLE public.inscripciones OWNER TO postgres;

--
-- Name: notas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notas (
    nota integer NOT NULL,
    desc_nota character varying(50),
    porcentaje numeric(5,2),
    posicion integer,
    cod_cur character varying(10),
    year integer,
    periodo integer,
    CONSTRAINT notas_periodo_check CHECK (((periodo >= 1) AND (periodo <= 2))),
    CONSTRAINT notas_posicion_check CHECK ((posicion > 0)),
    CONSTRAINT notas_year_check CHECK ((year > 0))
);


ALTER TABLE public.notas OWNER TO postgres;

--
-- Name: notas_nota_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.notas_nota_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notas_nota_seq OWNER TO postgres;

--
-- Name: notas_nota_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.notas_nota_seq OWNED BY public.notas.nota;


--
-- Name: calificaciones cod_cal; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificaciones ALTER COLUMN cod_cal SET DEFAULT nextval('public.calificaciones_cod_cal_seq'::regclass);


--
-- Name: notas nota; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notas ALTER COLUMN nota SET DEFAULT nextval('public.notas_nota_seq'::regclass);


--
-- Data for Name: calificaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calificaciones (cod_cal, nota, valor, fecha, cod_cur, cod_est, year, periodo) FROM stdin;
\.


--
-- Data for Name: cursos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cursos (cod_cur, nomb_cur, cod_doc) FROM stdin;
CUR0	BASES DE DATOS	DOC1
\.


--
-- Data for Name: cursosemestres; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cursosemestres (cod_cur, year, periodo) FROM stdin;
CUR0	2024	1
CUR0	2024	2
\.


--
-- Data for Name: docentes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.docentes (cod_doc, nomb_doc, clave) FROM stdin;
DOC0	root	root
DOC1	Jesus Reyes	12345678
\.


--
-- Data for Name: estudiantes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.estudiantes (cod_est, nomb_est, clave) FROM stdin;
10000100	Juan David Romero Villamizar	12345678
10000101	Astrid Daniela Martinez	12345678
10000102	Derly Lorena Novoa	12345678
10000103	Juan Carlos Bodoque	bodoqueelmejor
\.


--
-- Data for Name: inscripciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.inscripciones (cod_cur, cod_est, year, periodo) FROM stdin;
CUR0	10000100	2024	1
\.


--
-- Data for Name: notas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notas (nota, desc_nota, porcentaje, posicion, cod_cur, year, periodo) FROM stdin;
1	Parcial 1 2024	30.00	1	CUR0	2024	1
9	Parcial 1	30.00	1	CUR0	2024	2
11	Parcial 2	35.00	2	CUR0	2024	2
12	Parcial Final	35.00	3	CUR0	2024	2
\.


--
-- Name: calificaciones_cod_cal_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.calificaciones_cod_cal_seq', 3, true);


--
-- Name: notas_nota_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notas_nota_seq', 12, true);


--
-- Name: calificaciones calificaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificaciones
    ADD CONSTRAINT calificaciones_pkey PRIMARY KEY (cod_cal);


--
-- Name: cursos cursos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (cod_cur);


--
-- Name: cursosemestres cursosemestres_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cursosemestres
    ADD CONSTRAINT cursosemestres_pkey PRIMARY KEY (cod_cur, year, periodo);


--
-- Name: docentes docentes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.docentes
    ADD CONSTRAINT docentes_pkey PRIMARY KEY (cod_doc);


--
-- Name: estudiantes estudiantes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estudiantes
    ADD CONSTRAINT estudiantes_pkey PRIMARY KEY (cod_est);


--
-- Name: inscripciones inscripciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT inscripciones_pkey PRIMARY KEY (cod_cur, cod_est, year, periodo);


--
-- Name: notas notas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notas
    ADD CONSTRAINT notas_pkey PRIMARY KEY (nota);


--
-- Name: cursos trigger_generar_cur; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_generar_cur BEFORE INSERT ON public.cursos FOR EACH ROW EXECUTE FUNCTION public.set_cod_cur();


--
-- Name: docentes trigger_generar_doc; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_generar_doc BEFORE INSERT ON public.docentes FOR EACH ROW EXECUTE FUNCTION public.set_cod_doc();


--
-- Name: cursos trigger_renumerar_cursos; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_renumerar_cursos AFTER DELETE ON public.cursos FOR EACH STATEMENT EXECUTE FUNCTION public.renumerar_codigos_cursos();


--
-- Name: docentes trigger_renumerar_docentes; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_renumerar_docentes AFTER DELETE ON public.docentes FOR EACH STATEMENT EXECUTE FUNCTION public.renumerar_codigos_docentes();


--
-- Name: calificaciones fk_calificaciones_inscripciones; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificaciones
    ADD CONSTRAINT fk_calificaciones_inscripciones FOREIGN KEY (cod_cur, cod_est, year, periodo) REFERENCES public.inscripciones(cod_cur, cod_est, year, periodo) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: calificaciones fk_calificaciones_notas; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificaciones
    ADD CONSTRAINT fk_calificaciones_notas FOREIGN KEY (nota) REFERENCES public.notas(nota) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: cursos fk_cursos_docentes; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cursos
    ADD CONSTRAINT fk_cursos_docentes FOREIGN KEY (cod_doc) REFERENCES public.docentes(cod_doc) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: inscripciones fk_inscripciones_cursossu; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT fk_inscripciones_cursossu FOREIGN KEY (cod_cur, year, periodo) REFERENCES public.cursosemestres(cod_cur, year, periodo) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: inscripciones fk_inscripciones_estudiantescampo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT fk_inscripciones_estudiantescampo FOREIGN KEY (cod_est) REFERENCES public.estudiantes(cod_est) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notas fk_notas_cursos; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notas
    ADD CONSTRAINT fk_notas_cursos FOREIGN KEY (cod_cur, year, periodo) REFERENCES public.cursosemestres(cod_cur, year, periodo) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cursosemestres fk_semestres_cursos; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cursosemestres
    ADD CONSTRAINT fk_semestres_cursos FOREIGN KEY (cod_cur) REFERENCES public.cursos(cod_cur) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--
