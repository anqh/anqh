CREATE TABLE "public"."user_tokens" (
  "id" SERIAL,
  "user_id" INTEGER NOT NULL,
  "user_agent" VARCHAR(40) NOT NULL,
  "token" VARCHAR(32) NOT NULL,
  "created" INTEGER NOT NULL,
  "expires" INTEGER NOT NULL,
  CONSTRAINT "user_tokens_pkey" PRIMARY KEY("id"),
  CONSTRAINT "user_tokens_fk" FOREIGN KEY ("user_id")
    REFERENCES "public"."users"("id")
    ON DELETE CASCADE
    ON UPDATE NO ACTION
    NOT DEFERRABLE
) WITH OIDS;
