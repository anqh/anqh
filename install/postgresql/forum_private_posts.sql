CREATE TABLE "public"."forum_private_posts" (
  "id" SERIAL,
  "forum_topic_id" INTEGER NOT NULL,
  "forum_area_id" SMALLINT NOT NULL,
  "author_name" VARCHAR(50),
  "author_id" INTEGER,
  "post" TEXT,
  "author_ip" VARCHAR(15),
  "author_host" VARCHAR(150),
  "modify_count" SMALLINT DEFAULT 0,
  "attachment" VARCHAR(50),
  "parent_id" INTEGER,
  "created" INTEGER,
  "modified" INTEGER,
  CONSTRAINT "forum_private_posts_pkey" PRIMARY KEY("id"),
  CONSTRAINT "forum_posts_private_forum_topic_id" FOREIGN KEY ("forum_topic_id")
    REFERENCES "public"."forum_private_topics"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "forum_private_posts_author_id" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "forum_private_posts_parent_id" FOREIGN KEY ("parent_id")
    REFERENCES "public"."forum_private_posts"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "forum_private_posts_area_id_idx" ON "public"."forum_private_posts"
  USING btree ("forum_area_id");

CREATE INDEX "forum_private_posts_author_id_idx" ON "public"."forum_private_posts"
  USING btree ("author_id");

CREATE INDEX "forum_private_posts_topic_id_created" ON "public"."forum_private_posts"
  USING btree ("forum_topic_id", "created");
