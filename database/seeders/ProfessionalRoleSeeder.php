<?php

namespace Database\Seeders;

use App\Models\Committee;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Seeder;

class ProfessionalRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $committees = Committee::select('id', 'code')->get();

        $roles = [
            [
                'name' => 'Mergulhador Científico',
                'code' => 'SCIENTIFICDIVER',
                'role' => 'DIVER',
                'committee_id' => $committees->where('code', 'SCIENTIFIC')->value('id'),
            ],
            [
                'name' => 'Instrutor Científico',
                'code' => 'SCIENTIFICINSTRUCTOR',
                'role' => 'INSTRUCTOR',
                'committee_id' => $committees->where('code', 'SCIENTIFIC')->value('id'),
            ],
            [
                'name' => 'Mergulhador de Especialidade Científica',
                'code' => 'SCIENTIFICSPECIALITYDIVER',
                'role' => 'DIVER',
                'committee_id' => $committees->where('code', 'SCIENTIFIC')->value('id'),
            ],
            [
                'name' => 'Instrutor de Especialidade Científica',
                'code' => 'SCIENTIFICSPECIALITYINSTRUCTOR',
                'role' => 'INSTRUCTOR',
                'committee_id' => $committees->where('code', 'SCIENTIFIC')->value('id'),
            ],
            [
                'name' => 'Líder de Mergulho Científico',
                'code' => 'SCIENTIFICDIVELEADER',
                'role' => 'LEADER',
                'committee_id' => $committees->where('code', 'SCIENTIFIC')->value('id'),
            ],
            [
                'name' => 'Líder de Mergulho',
                'code' => 'DIVELEADER',
                'role' => 'LEADER',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Mergulhador',
                'code' => 'DIVER',
                'role' => 'DIVER',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Instrutor de Mergulho',
                'code' => 'DIVINGINSTRUCTOR',
                'role' => 'INSTRUCTOR',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Mergulhador Livre',
                'code' => 'FREEDIVER',
                'role' => 'DIVER',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Instrutor de Mergulho Livre',
                'code' => 'FREEDIVERINSTRUCTOR',
                'role' => 'INSTRUCTOR',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Mergulhador Técnico',
                'code' => 'TECHNICALDIVER',
                'role' => 'DIVER',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Instrutor de Mergulho Técnico',
                'code' => 'TECHNICALDIVINGINSTRUCTOR',
                'role' => 'INSTRUCTOR',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
            [
                'name' => 'Treinador de Aquatlo',
                'code' => 'AQUATHLONCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Natação com Barbatanas',
                'code' => 'FINSWIMMINGCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Mergulho Livre',
                'code' => 'FREEDIVINGCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Orientação',
                'code' => 'ORIENTEERINGCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Pesca Submarina',
                'code' => 'SPEARFISHINGCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Mergulho Desportivo',
                'code' => 'SPORTDIVINGCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Tiro ao Alvo',
                'code' => 'TARGETSHOOTINGCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Hóquei Subaquático',
                'code' => 'UNDERWATERHOCKEYCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Imagem',
                'code' => 'VISUALCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Árbitro de Aquatlo',
                'code' => 'AQUATHLONREFEREE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Juiz de Natação com Barbatanas',
                'code' => 'FINSWIMMINGJUDGE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Juiz de Mergulho Livre',
                'code' => 'FREEDIVINGJUDGE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Árbitro de Orientação',
                'code' => 'ORIENTEERINGREFEREE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Juiz de Pesca Submarina',
                'code' => 'SPEARFISHINGJUDGE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Juiz de Mergulho Desportivo',
                'code' => 'SPORTDIVINGJUDGE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Juiz de Tiro ao Alvo',
                'code' => 'TARGETSHOOTINGJUDGE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Árbitro de Hóquei Subaquático',
                'code' => 'UNDERWATERHOCKEYREFEREE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Árbitro de Rugby Subaquático',
                'code' => 'UNDERWATERRUGBYREFEREE',
                'role' => 'TECHNICAL_OFFICIAL',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Treinador de Rugby Subaquático',
                'code' => 'UNDERWATERRUGBYCOACH',
                'role' => 'COACH',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Atleta',
                'code' => 'ATHLETE',
                'role' => 'ATHLETE',
                'committee_id' => $committees->where('code', 'SPORT')->value('id'),
            ],
            [
                'name' => 'Profissional de Mergulho',
                'code' => 'DIVINGPROFESSIONAL',
                'role' => 'DIVINGPROFESSIONAL',
                'committee_id' => $committees->where('code', 'DIVING')->value('id'),
            ],
        ];

        foreach ($roles as $role) {
            ProfessionalRole::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
