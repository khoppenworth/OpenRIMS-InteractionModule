INSERT INTO interactions (drug_a_name, drug_a_atc, drug_b_name, drug_b_atc, severity, description, clinical_management, evidence_level, source_url) VALUES
('Warfarin', 'B01AA03', 'Trimethoprim', 'J01EA01', 'major', 'Trimethoprim potentiates the anticoagulant effect of warfarin leading to elevated INR.', 'Avoid combination when possible; increase INR monitoring if co-administration is required.', 'High', 'https://www.interaktionsdatabasen.dk'),
('Simvastatin', 'C10AA01', 'Clarithromycin', 'J01FA09', 'major', 'CYP3A4 inhibition by clarithromycin increases simvastatin plasma concentrations.', 'Hold simvastatin during macrolide treatment or switch to a non-interacting statin.', 'High', 'https://www.interaktionsdatabasen.dk'),
('Metformin', 'A10BA02', 'Iodinated contrast media', 'V08AB', 'moderate', 'Contrast-induced nephropathy may reduce metformin clearance increasing risk of lactic acidosis.', 'Withhold metformin on day of procedure and reassess renal function 48 hours post exposure.', 'Moderate', 'https://www.interaktionsdatabasen.dk'),
('Sertraline', 'N06AB06', 'Linezolid', 'J01XX08', 'major', 'Risk of serotonin syndrome due to MAO inhibition by linezolid.', 'Avoid combination; if unavoidable, monitor closely for serotonin toxicity.', 'Moderate', 'https://www.interaktionsdatabasen.dk'),
('Levothyroxine', 'H03AA01', 'Calcium carbonate', 'A12AA04', 'minor', 'Calcium may reduce levothyroxine absorption.', 'Separate administration by at least 4 hours and monitor TSH levels.', 'Low', 'https://www.interaktionsdatabasen.dk');

INSERT INTO users (name, email, password, role) VALUES
('System Administrator', 'admin@example.com', '$2y$12$6Bpe8ahWOKnGUERcnbjIB.C69TwWMypOj5.1j2GW6MvNQxkRVtxwe', 'admin'),
('Clinical Staff', 'staff@example.com', '$2y$12$N.mSuAtIFHybqh1baWceJ.jJ1U/V3Za9GrckUPzP3876itEpKIM5G', 'staff');
