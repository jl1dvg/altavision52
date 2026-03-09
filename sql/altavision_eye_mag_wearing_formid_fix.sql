-- Fix EyeMag Subjetivo persistence with large form ids
ALTER TABLE `form_eye_mag_wearing`
  MODIFY `FORM_ID` bigint(20) NOT NULL;
