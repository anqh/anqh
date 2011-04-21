CREATE TABLE "public"."forum_topics" (
  "id" SERIAL,
  "forum_area_id" INTEGER NOT NULL,
  "name" VARCHAR(100) NOT NULL,
  "author_name" VARCHAR(30),
  "created" INTEGER,
  "read_count" INTEGER DEFAULT 0,
  "type" INTEGER DEFAULT 0,
  "post_count" INTEGER DEFAULT 0,
  "last_post_id" INTEGER,
  "last_poster" VARCHAR(30),
  "first_post_id" INTEGER,
  "old_name" VARCHAR(200),
  "sticky" INTEGER DEFAULT 0,
  "bind_id" INTEGER,
  "expire" DATE,
  "last_posted" INTEGER,
  "author_id" INTEGER,
  "votes" INTEGER DEFAULT 0,
  "points" INTEGER DEFAULT 0,
  "read_only" SMALLINT,
  "status" SMALLINT DEFAULT 0,
  "old_area" INTEGER,
  CONSTRAINT "forum_topics_pkey" PRIMARY KEY("id"),
  CONSTRAINT "forum_topics_aid_fkey" FOREIGN KEY ("forum_area_id")
    REFERENCES "public"."forum_areas"("id")
    ON DELETE RESTRICT
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "forum_topics_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "forum_topics_first_post_id" FOREIGN KEY ("first_post_id")
    REFERENCES "public"."forum_posts"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "forum_topics_last_post_id" FOREIGN KEY ("last_post_id")
    REFERENCES "public"."forum_posts"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "forum_topics_aid" ON "public"."forum_topics"
  USING btree ("forum_area_id");

CREATE INDEX "forum_topics_last_post" ON "public"."forum_topics"
  USING btree ("last_post_id");

CREATE INDEX "forum_topics_last_posted2" ON "public"."forum_topics"
  USING btree ("last_posted");

CREATE INDEX "forum_topics_points" ON "public"."forum_topics"
  USING btree ("points");

CREATE INDEX "forum_topics_sticky" ON "public"."forum_topics"
  USING btree ("sticky");

CREATE INDEX "forum_topics_votes" ON "public"."forum_topics"
  USING btree ("votes");
