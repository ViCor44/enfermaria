ALTER TABLE park_schedule_assignments
  ADD COLUMN shift_start_time TIME NULL AFTER shift_type,
  ADD COLUMN shift_end_time TIME NULL AFTER shift_start_time;