CREATE TABLE "public"."geo_cities" (
  "id" INTEGER NOT NULL,
  "name" VARCHAR(200) NOT NULL,
  "geo_country_id" INTEGER NOT NULL,
  "latitude" NUMERIC(8,4),
  "longitude" NUMERIC(8,4),
  "population" INTEGER,
  "geo_timezone_id" VARCHAR(64),
  "created" INTEGER,
  "modified" INTEGER,
  "i18n" TEXT,
  CONSTRAINT "geo_cities_pkey" PRIMARY KEY("id"),
  CONSTRAINT "geo_cities_geo_country_id" FOREIGN KEY ("geo_country_id")
    REFERENCES "public"."geo_countries"("id")
    ON DELETE RESTRICT
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "geo_cities_geo_timezone_id" FOREIGN KEY ("geo_timezone_id")
    REFERENCES "public"."geo_timezones"("id")
    ON DELETE NO ACTION
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITH OIDS;
