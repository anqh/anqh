CREATE TABLE "public"."venues" (
  "id" SERIAL,
  "name" VARCHAR(100) NOT NULL,
  "homepage" VARCHAR(100),
  "description" VARCHAR(250),
  "address" VARCHAR(50),
  "city_name" VARCHAR(50),
  "city_id" INTEGER,
  "zip" VARCHAR(5),
  "latitude" DOUBLE PRECISION,
  "longitude" DOUBLE PRECISION,
  "author_id" INTEGER,
  "event_host" SMALLINT,
  "hours" VARCHAR(250),
  "info" TEXT,
  "default_image_id" INTEGER,
  "forum_topic_id" INTEGER,
  "created" INTEGER,
  "modified" INTEGER,
  "geo_city_id" INTEGER,
  "geo_country_id" INTEGER,
  "foursquare_id" INTEGER,
  "foursquare_category_id" INTEGER,
  CONSTRAINT "venues_pkey" PRIMARY KEY("id"),
  CONSTRAINT "venues_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "venues_default_image_id" FOREIGN KEY ("default_image_id")
    REFERENCES "public"."images"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "venues_forum_topic_id" FOREIGN KEY ("forum_topic_id")
    REFERENCES "public"."forum_topics"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;
