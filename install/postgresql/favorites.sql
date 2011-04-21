CREATE TABLE "public"."favorites" (
  "user_id" INTEGER NOT NULL,
  "event_id" INTEGER NOT NULL,
  "added" TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
  "id" SERIAL,
  "created" INTEGER DEFAULT date_part('epoch'::text, now()),
  CONSTRAINT "favorites_pkey" PRIMARY KEY("id"),
  CONSTRAINT "favorites_event_id_fkey" FOREIGN KEY ("event_id")
    REFERENCES "public"."events"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE,
  CONSTRAINT "favorites_user_id_fkey" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "favorites_event_id" ON "public"."favorites"
  USING btree ("event_id");

CREATE INDEX "favorites_member_id" ON "public"."favorites"
  USING btree ("user_id");
