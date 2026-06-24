<?php

namespace App\Enums;

enum CertificationCategoryEnum: string
{
    case DiverCareer = 'Diver Career';
    case RecreationalDiverSpeciality = 'Recreational Diver Speciality';
    case TechnicalDiverSpeciality = 'Technical Diver Speciality';
    case InstructorCareer = 'Instructor and Dive Leader';
    case RecreationalInstructorSpeciality = 'Recreational Instructor Speciality';
    case TechnicalInstructorSpeciality = 'Technical Instructor Speciality';
    case ScientificDiverCareer = 'Scientific Diver Career';
    case ScientificSpecialityDiver = 'Scientific Speciality Diver';
    case ScientificSpecialityInstructor = 'Scientific Speciality Instructor';
    case ScientificInstructorCareer = 'Scientific Instructor Career';
    case Freediver = 'Freediver';
    case FreedivingInstructor = 'Freediving Instructor';
    case Coach = 'Coach';
    case RefereeJudge = 'Referee / Judge';

    // Gas Blender and Dive Technician category
    case GasBlender = 'Gas Blender and Dive Technician';

}
