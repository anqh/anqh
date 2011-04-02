CREATE TABLE "public"."exifs" (
  "id" SERIAL,
  "image_id" INTEGER,
  "make" VARCHAR(64),
  "model" VARCHAR(64),
  "exposure" VARCHAR(25),
  "aperture" VARCHAR(10),
  "focal" VARCHAR(10),
  "iso" SMALLINT,
  "taken" TIMESTAMP WITHOUT TIME ZONE,
  "flash" VARCHAR(64),
  "program" VARCHAR(64),
  "metering" VARCHAR(64),
  "latitude" NUMERIC(10,6),
  "latitude_ref" VARCHAR(1),
  "longitude" NUMERIC(10,6),
  "longitude_ref" VARCHAR(1),
  "altitude" VARCHAR(16),
  "altitude_ref" VARCHAR(16),
  "lens" VARCHAR(64),
  CONSTRAINT "exifs_pkey" PRIMARY KEY("id"),
  CONSTRAINT "exifs_image_id" FOREIGN KEY ("image_id")
    REFERENCES "public"."images"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "exifs_image_id_idx" ON "public"."exifs"
  USING btree ("image_id");
