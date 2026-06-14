import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { COLORS } from '../theme/theme';
import DashboardScreen from '../screens/DashboardScreen';
import SuratMasukNavigator from './SuratMasukNavigator';
import SuratKeluarNavigator from './SuratKeluarNavigator';
import DisposisiNavigator from './DisposisiNavigator';
import ProfilScreen from '../screens/ProfilScreen';

export type MainTabParamList = {
  DashboardTab: undefined;
  SuratMasukTab: undefined;
  SuratKeluarTab: undefined;
  DisposisiTab: undefined;
  ProfilTab: undefined;
};

const Tab = createBottomTabNavigator<MainTabParamList>();

// Custom Tab Bar Icon Renderer menggunakan Emojis & styled container
interface TabBarIconProps {
  focused: boolean;
  icon: string;
}

const TabIcon = ({ focused, icon }: TabBarIconProps) => (
  <View style={[styles.iconContainer, focused && styles.iconContainerFocused]}>
    <Text style={[styles.iconText, focused && styles.iconTextFocused]}>{icon}</Text>
  </View>
);

export default function MainTabNavigator() {
  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: COLORS.primary,
        tabBarInactiveTintColor: COLORS.textMuted,
        tabBarStyle: styles.tabBar,
        tabBarLabelStyle: styles.tabBarLabel,
      }}
    >
      <Tab.Screen
        name="DashboardTab"
        component={DashboardScreen}
        options={{
          tabBarLabel: 'Dashboard',
          tabBarIcon: ({ focused }: { focused: boolean }) => <TabIcon focused={focused} icon="📊" />,
        }}
      />
      <Tab.Screen
        name="SuratMasukTab"
        component={SuratMasukNavigator}
        options={{
          tabBarLabel: 'Surat Masuk',
          tabBarIcon: ({ focused }: { focused: boolean }) => <TabIcon focused={focused} icon="📬" />,
        }}
      />
      <Tab.Screen
        name="SuratKeluarTab"
        component={SuratKeluarNavigator}
        options={{
          tabBarLabel: 'Surat Keluar',
          tabBarIcon: ({ focused }: { focused: boolean }) => <TabIcon focused={focused} icon="📤" />,
        }}
      />
      <Tab.Screen
        name="DisposisiTab"
        component={DisposisiNavigator}
        options={{
          tabBarLabel: 'Disposisi',
          tabBarIcon: ({ focused }: { focused: boolean }) => <TabIcon focused={focused} icon="📋" />,
        }}
      />
      <Tab.Screen
        name="ProfilTab"
        component={ProfilScreen}
        options={{
          tabBarLabel: 'Profil',
          tabBarIcon: ({ focused }: { focused: boolean }) => <TabIcon focused={focused} icon="👤" />,
        }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  tabBar: {
    backgroundColor: COLORS.white,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    height: 60,
    paddingBottom: 6,
    paddingTop: 6,
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
  },
  tabBarLabel: {
    fontSize: 11,
    fontWeight: '600',
    marginTop: 2,
  },
  iconContainer: {
    width: 40,
    height: 28,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'transparent',
  },
  iconContainerFocused: {
    backgroundColor: 'rgba(37, 87, 167, 0.1)',
  },
  iconText: {
    fontSize: 18,
    opacity: 0.7,
  },
  iconTextFocused: {
    opacity: 1,
  },
});
