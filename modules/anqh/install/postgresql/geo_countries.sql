CREATE TABLE "public"."geo_countries" (
  "id" INTEGER NOT NULL,
  "name" VARCHAR(200) NOT NULL,
  "code" VARCHAR(2) NOT NULL,
  "currency" VARCHAR(3),
  "population" INTEGER,
  "created" INTEGER,
  "modified" INTEGER,
  "i18n" TEXT,
  CONSTRAINT "geo_countries_code_key" UNIQUE("code"),
  CONSTRAINT "geo_countries_name_key" UNIQUE("name"),
  CONSTRAINT "geo_countries_pkey" PRIMARY KEY("id")
) WITH OIDS;
