CREATE TABLE "public"."blog_entries" (
  "id" INTEGER DEFAULT nextval('blog_id_seq'::regclass) NOT NULL,
  "author_id" INTEGER NOT NULL,
  "name" VARCHAR(250) NOT NULL,
  "content" TEXT,
  "view_count" INTEGER DEFAULT 0,
  "modify_count" SMALLINT DEFAULT 0,
  "comment_count" INTEGER DEFAULT 0,
  "new_comment_count" INTEGER DEFAULT 0,
  "created" INTEGER,
  "modified" INTEGER,
  CONSTRAINT "blog_pkey" PRIMARY KEY("id"),
  CONSTRAINT "blog_user_id_fkey" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "blog_entry_user_id" ON "public"."blog_entries"
  USING btree ("author_id");

CREATE INDEX "blog_new_comment_count" ON "public"."blog_entries"
  USING btree ("new_comment_count");

CREATE INDEX "blog_view_count" ON "public"."blog_entries"
  USING btree ("view_count");
