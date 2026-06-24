This guide helps configure events correctly so they appear as intended in the **Federation portal's event list**.

To ensure an event is visible in the Federation list, check these settings:

1.  **Make it Visible (`Is Visible` checkbox):**
    *   **Must be checked.** If unchecked, the event is hidden everywhere.

2.  **Set the Right Status (`Event Status` dropdown):**
    *   Use **`Active`** or **`Preparation`**. These appear in the main Federation list.
    *   `Archived` and `Candidacy` statuses will hide the event from this list.

3.  **Choose Federation-Friendly Enrollment (`Enrollment Type` dropdown):**
    *   **Avoid `Only Individuals` and `Only Entities`**. Selecting these hides the event from the Federation list, as that list is for events Federations interact with.
    *   Use types like `Only Federations` or others permitting federation involvement.

4.  **Configure Geographical Coverage Correctly:** **(Crucial!)**
    *   **Global Event?** Set `Event Geographical Coverage` to **`International`**. Do *not* link specific Countries or Zones below if it's truly international.
    *   **National/Regional Event?**
        *   Set `Event Geographical Coverage` to **`National`**.
        *   **You MUST** then select the specific Countries, Geo Zones, or Sub Regions this event applies to in the corresponding selection boxes below.
    *   **Common Mistake:** **DO NOT** leave `Event Geographical Coverage` blank/`NULL` or set to `International` if you link specific Countries/Zones below. If you do, federations from those specific countries **WILL NOT** see the event when they filter. The system needs the `National` setting to check the linked countries/zones.
    *   **Implicitly Global:** If `Event Geographical Coverage` is left blank/`NULL` **AND** you do **NOT** link any specific Countries/Zones, it's treated as open to everyone (like `International`).

5.  **Dates (`Start Date`, `End Date`):**
    *   Ensure the `End Date` is today or later for the event to appear in the default "Upcoming" view for federations. Past events require the "Past" filter.

6.  **Category (`Event Category`):**
    *   Set appropriately (`Organization`, `Competition`, etc.). Federations use this to filter the list.

Carefully setting these fields, especially geographical coverage, controls which events appear for which federations. 

---

### Competition-Specific Settings

When `Event Category` is set to **`Competition`**, the following settings become available and are crucial for defining the sporting event:

*   **Sport:** Select the primary sport for this competition. This choice influences available disciplines.
*   **Discipline Template:** (Optional) Selecting a template can pre-fill the standard disciplines for this type of competition.
*   **Competition Types:** Choose one or more official types (e.g., World Championship, Continental Cup).
*   **Technical Delegate:** Provide details if a Technical Delegate is assigned.
*   **Anti-Doping Info:** Record anti-doping status and contact information if applicable.

#### Enrollment Requirements & Limits:

These settings control who can participate and under what conditions:

*   **Required Athlete Licenses:** Select specific licenses that athletes **must** possess to be enrolled.
*   **Required Coach Certifications:** Select specific certifications that coaches **must** possess.
*   **Required Referee Certifications:** Select specific certifications that referees **must** possess.
*   **ADEL Certification Required?** Check the respective boxes if **Athletes**, **Coaches**, or **Referees** must have a valid WADA ADEL certificate.
*   **Enrollment Limits:**
    *   `Max Disciplines per Athlete`: Set the maximum number of individual disciplines an athlete can enter.
    *   `Max Relays per Athlete`: Set the maximum number of relay teams an athlete can be part of.
    *   `Max Teams per Athlete`: Set the maximum number of teams (e.g., in Water Polo) an athlete can be part of.
