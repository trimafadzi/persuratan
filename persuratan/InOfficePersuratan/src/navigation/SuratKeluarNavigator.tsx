import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import SuratKeluarListScreen from '../screens/SuratKeluarListScreen';
import SuratKeluarDetailScreen from '../screens/SuratKeluarDetailScreen';
import SuratKeluarCreateScreen from '../screens/SuratKeluarCreateScreen';

export type SuratKeluarStackParamList = {
  SuratKeluarList: undefined;
  SuratKeluarDetail: { id: number };
  SuratKeluarCreate: undefined;
};

const Stack = createNativeStackNavigator<SuratKeluarStackParamList>();

export default function SuratKeluarNavigator() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        animation: 'slide_from_right',
      }}
    >
      <Stack.Screen name="SuratKeluarList" component={SuratKeluarListScreen} />
      <Stack.Screen name="SuratKeluarDetail" component={SuratKeluarDetailScreen} />
      <Stack.Screen name="SuratKeluarCreate" component={SuratKeluarCreateScreen} />
    </Stack.Navigator>
  );
}
