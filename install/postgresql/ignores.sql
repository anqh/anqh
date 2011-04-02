CREATE TABLE "public"."ignores" (
  "id" SERIAL,
  "user_id" INTEGER NOT NULL,
  "ignore_id" INTEGER NOT NULL,
  "created" INTEGER DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT "ignores_pkey" PRIMARY KEY("id"),
  CONSTRAINT "ignores_ignore_id_fkey" FOREIGN KEY ("ignore_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE,
  CONSTRAINT "ignores_user_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "ignores_ignore_id_idx" ON "public"."ignores"
  USING btree ("ignore_id");

CREATE UNIQUE INDEX "ignores_user_id_idx" ON "public"."ignores"
  USING btree ("user_id", "ignore_id");
