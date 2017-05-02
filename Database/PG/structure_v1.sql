CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


CREATE TABLE jobs (
    id uuid DEFAULT uuid_generate_v4() NOT NULL,
    type integer NOT NULL,
    url text NOT NULL,
    hash text NOT NULL,
    locked boolean DEFAULT false NOT NULL,
    date_added timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_type CHECK ((type = ANY (ARRAY[1, 2])))
);

ALTER TABLE ONLY jobs
    ADD CONSTRAINT pk_jobs PRIMARY KEY (id);

ALTER TABLE ONLY jobs
    ADD CONSTRAINT unq_hash UNIQUE (hash);

CREATE TABLE public.crawlers
(
   id uuid NOT NULL DEFAULT uuid_generate_v4(), 
   allowed_ip text NOT NULL DEFAULT '127.0.0.1', 
   date_added timestamp without time zone NOT NULL DEFAULT now(), 
   last_call timestamp without time zone, 
   CONSTRAINT pk_crawlers PRIMARY KEY (id)
) 
WITH (
  OIDS = FALSE
)
;

ALTER TABLE jobs
  ADD COLUMN crawler_id uuid NOT NULL;
ALTER TABLE jobs
  ADD CONSTRAINT fk_crawler FOREIGN KEY (crawler_id) REFERENCES crawlers (id) ON UPDATE CASCADE ON DELETE RESTRICT;
  
ALTER TABLE jobs
  ADD COLUMN done_by uuid;
ALTER TABLE jobs
  ADD CONSTRAINT fk_crawler_done FOREIGN KEY (done_by) REFERENCES crawlers (id) ON UPDATE CASCADE ON DELETE RESTRICT;
