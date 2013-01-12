CREATE TABLE "public"."blog_comments" (
  "id" INTEGER DEFAULT nextval('blog_comments_id_seq'::regclass) NOT NULL,
  "blog_entry_id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "author_id" INTEGER NOT NULL,
  "comment" VARCHAR(255) NOT NULL,
  "private" SMALLINT DEFAULT 0 NOT NULL,
  "created" INTEGER,
  CONSTRAINT "blog_comments_pkey" PRIMARY KEY("id"),
  CONSTRAINT "blog_comments_blog_id_fkey" FOREIGN KEY ("blog_entry_id")
    REFERENCES "public"."blog_entries"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "blog_comments_user_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "blog_comments_author_id_fkey" FOREIGN KEY ("author_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "blog_comments_blog_id" ON "public"."blog_comments"
  USING btree ("blog_entry_id");

CREATE INDEX "blog_comments_user_id" ON "public"."blog_comments"
  USING btree ("user_id");

CREATE INDEX "blog_comments_author_id" ON "public"."blog_comments"
  USING btree ("author_id");
